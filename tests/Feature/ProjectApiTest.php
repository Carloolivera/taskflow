<?php

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ==========================================
// AUTH TESTS
// ==========================================

test('unauthenticated user cannot access projects api', function () {
    $this->getJson('/api/projects')->assertUnauthorized();
});

// ==========================================
// INDEX TESTS
// ==========================================

test('user can list their projects', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(); // other user's projects

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/projects');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can search projects by name', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Alpha']);
    Project::factory()->create(['user_id' => $user->id, 'name' => 'Beta']);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects?search=Alpha')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('user can filter projects by status', function () {
    $user = User::factory()->create();
    Project::factory()->active()->create(['user_id' => $user->id]);
    Project::factory()->completed()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects?status=active')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

// ==========================================
// STORE TESTS
// ==========================================

test('user can create a project via api', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/projects', [
        'name' => 'New Project',
        'description' => 'A test project',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'New Project');

    $this->assertDatabaseHas('projects', [
        'name' => 'New Project',
        'user_id' => $user->id,
    ]);
});

test('create project requires name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/projects', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('create project validates status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/projects', [
        'name' => 'Test',
        'status' => 'invalid',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

// ==========================================
// SHOW TESTS
// ==========================================

test('user can view their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id)
        ->assertOk()
        ->assertJsonPath('name', $project->name);
});

test('user cannot view other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $otherProject->id)
        ->assertForbidden();
});

// ==========================================
// UPDATE TESTS
// ==========================================

test('user can update their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->putJson('/api/projects/' . $project->id, [
        'name' => 'Updated Name',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

test('user cannot update other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    Sanctum::actingAs($user);

    $this->putJson('/api/projects/' . $otherProject->id, [
        'name' => 'Hacked',
    ])->assertForbidden();
});

// ==========================================
// DELETE TESTS
// ==========================================

test('user can delete their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->deleteJson('/api/projects/' . $project->id)->assertOk();

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

test('user cannot delete other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    Sanctum::actingAs($user);

    $this->deleteJson('/api/projects/' . $otherProject->id)->assertForbidden();
});
