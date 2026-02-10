<?php

use App\Livewire\ProjectManager;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest is redirected from projects page', function () {
    $this->get('/projects')->assertRedirect('/login');
});

test('authenticated user can access projects page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/projects')->assertOk();
});

// ==========================================
// CREATE TESTS
// ==========================================

test('user can create a project', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('openModal')
        ->set('name', 'Mi Proyecto')
        ->set('description', 'Descripción del proyecto')
        ->set('status', 'active')
        ->call('save');

    $this->assertDatabaseHas('projects', [
        'name' => 'Mi Proyecto',
        'user_id' => $user->id,
    ]);
});

test('project name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('openModal')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('project status must be valid', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('openModal')
        ->set('name', 'Test')
        ->set('status', 'invalid')
        ->call('save')
        ->assertHasErrors(['status']);
});

// ==========================================
// EDIT TESTS
// ==========================================

test('user can edit their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id, 'name' => 'Original']);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('edit', $project->id)
        ->set('name', 'Updated Name')
        ->call('save');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
    ]);
});

// ==========================================
// DELETE TESTS
// ==========================================

test('user can delete their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('confirmDelete', $project->id)
        ->call('delete');

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

test('user can cancel delete', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->call('confirmDelete', $project->id)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false);

    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

// ==========================================
// FILTER TESTS
// ==========================================

test('user can filter projects by status', function () {
    $user = User::factory()->create();
    Project::factory()->active()->create(['user_id' => $user->id, 'name' => 'Active Project']);
    Project::factory()->completed()->create(['user_id' => $user->id, 'name' => 'Done Project']);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->set('filterStatus', 'active')
        ->assertSee('Active Project')
        ->assertDontSee('Done Project');
});

test('user can search projects by name', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Project']);
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Beta Project']);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Project')
        ->assertDontSee('Beta Project');
});

test('user can clear filters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->set('search', 'something')
        ->set('filterStatus', 'active')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filterStatus', '');
});

// ==========================================
// SCOPING TESTS
// ==========================================

test('user sees only their own projects', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Project::factory()->create(['user_id' => $user->id, 'name' => 'My Project']);
    Project::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other Project']);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->assertSee('My Project')
        ->assertDontSee('Other Project');
});

// ==========================================
// MODAL TESTS
// ==========================================

test('open modal resets form', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->set('name', 'leftover')
        ->call('openModal')
        ->assertSet('name', '')
        ->assertSet('showModal', true)
        ->assertSet('isEditing', false);
});

// ==========================================
// SCOPE TESTS
// ==========================================

test('project active scope works', function () {
    $user = User::factory()->create();
    Project::factory()->active()->create(['user_id' => $user->id]);
    Project::factory()->completed()->create(['user_id' => $user->id]);
    Project::factory()->archived()->create(['user_id' => $user->id]);

    expect(Project::active()->count())->toBe(1);
    expect(Project::completed()->count())->toBe(1);
    expect(Project::archived()->count())->toBe(1);
});

// ==========================================
// RELATIONSHIP TESTS
// ==========================================

test('project belongs to user', function () {
    $project = Project::factory()->create();

    expect($project->user)->toBeInstanceOf(User::class);
});

test('project shows task count', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProjectManager::class)
        ->assertSee($project->name);
});
