<?php

use App\Livewire\Dashboard;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest cannot access dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');
});

// ==========================================
// STATS TESTS
// ==========================================

test('dashboard shows project counts', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->active()->create(['user_id' => $user->id]);
    Project::factory()->archived()->create(['user_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('totalProjects'))->toBe(4);
    expect($component->viewData('activeProjects'))->toBe(3);
});

test('dashboard shows task counts', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Task::factory()->count(2)->pending()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->inProgress()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->completed()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('totalTasks'))->toBe(4);
    expect($component->viewData('pendingTasks'))->toBe(2);
    expect($component->viewData('inProgressTasks'))->toBe(1);
    expect($component->viewData('completedTasks'))->toBe(1);
});

test('dashboard shows overdue task count', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Task::factory()->overdue()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->pending()->create(['project_id' => $project->id, 'user_id' => $user->id, 'due_date' => now()->addDays(5)]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('overdueTasks'))->toBe(1);
});

test('dashboard shows zero counts when no data', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('totalProjects'))->toBe(0);
    expect($component->viewData('totalTasks'))->toBe(0);
    expect($component->viewData('pendingTasks'))->toBe(0);
});

test('dashboard only shows current user data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Project::factory()->count(2)->create(['user_id' => $user->id]);
    Project::factory()->count(3)->create(['user_id' => $otherUser->id]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('totalProjects'))->toBe(2);
});

// ==========================================
// ADMIN STATS TESTS
// ==========================================

test('admin dashboard shows total users count', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $component = Livewire::actingAs($admin)->test(Dashboard::class);

    expect($component->viewData('totalUsers'))->toBe(3);
});

test('admin dashboard shows total tags count', function () {
    $admin = User::factory()->admin()->create();
    Tag::factory()->count(5)->create();

    $component = Livewire::actingAs($admin)->test(Dashboard::class);

    expect($component->viewData('totalTags'))->toBe(5);
});

test('regular user does not see admin stats', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('isAdmin'))->toBeFalse();
});

// ==========================================
// RECENT TASKS TESTS
// ==========================================

test('dashboard shows recent tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'My Recent Task']);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('My Recent Task');
});

test('dashboard shows max 5 recent tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->count(7)->create(['project_id' => $project->id, 'user_id' => $user->id]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->viewData('recentTasks'))->toHaveCount(5);
});

test('dashboard shows task project name', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id, 'name' => 'My Project']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('My Project');
});

test('dashboard shows empty state when no tasks', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('No hay tareas todavia');
});

// ==========================================
// QUICK ACTIONS TESTS
// ==========================================

test('dashboard shows quick action links', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Gestionar Proyectos');
});

test('admin sees admin quick actions', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('Gestionar Etiquetas');
});

test('regular user does not see admin quick actions', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertDontSee('Gestionar Etiquetas');
});

// ==========================================
// NAVIGATION TESTS
// ==========================================

test('navigation shows projects link for all users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSee('Proyectos');
});

test('navigation shows tags link for admin only', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertSee('Etiquetas');
});

test('navigation hides tags link for regular user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertDontSee('Etiquetas');
});

test('admin sees role badge in navigation', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertSee('Admin');
});
