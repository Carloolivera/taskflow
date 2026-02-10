<?php

use App\Livewire\TaskManager;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest is redirected from tasks page', function () {
    $project = Project::factory()->create();

    $this->get('/projects/' . $project->id . '/tasks')->assertRedirect('/login');
});

test('user can access their project tasks page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/projects/' . $project->id . '/tasks')
        ->assertOk();
});

test('user cannot access other users project tasks', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    $this->actingAs($user)
        ->get('/projects/' . $otherProject->id . '/tasks')
        ->assertForbidden();
});

// ==========================================
// CREATE TESTS
// ==========================================

test('user can create a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('openModal')
        ->set('title', 'New Task')
        ->set('description', 'Task description')
        ->set('status', 'pending')
        ->set('priority', 'high')
        ->call('save');

    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'priority' => 'high',
    ]);
});

test('user can create a task with tags', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create();

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('openModal')
        ->set('title', 'Tagged Task')
        ->set('selectedTags', [$tag->id])
        ->call('save');

    $task = Task::where('title', 'Tagged Task')->first();
    expect($task->tags)->toHaveCount(1);
    expect($task->tags->first()->id)->toBe($tag->id);
});

test('task title is required', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('openModal')
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title']);
});

test('task status must be valid', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('openModal')
        ->set('title', 'Test')
        ->set('status', 'invalid')
        ->call('save')
        ->assertHasErrors(['status']);
});

test('task priority must be valid', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('openModal')
        ->set('title', 'Test')
        ->set('priority', 'invalid')
        ->call('save')
        ->assertHasErrors(['priority']);
});

// ==========================================
// EDIT TESTS
// ==========================================

test('user can edit a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Old']);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('edit', $task->id)
        ->set('title', 'Updated Title')
        ->call('save');

    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated Title']);
});

// ==========================================
// DELETE TESTS
// ==========================================

test('user can delete a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('confirmDelete', $task->id)
        ->call('delete');

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('user can cancel delete task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->call('confirmDelete', $task->id)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false);

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

// ==========================================
// FILTER TESTS
// ==========================================

test('user can filter tasks by status', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->pending()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Pending Task']);
    Task::factory()->completed()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Done Task']);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('filterStatus', 'pending')
        ->assertSee('Pending Task')
        ->assertDontSee('Done Task');
});

test('user can filter tasks by priority', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Urgent One', 'priority' => 'urgent']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Low One', 'priority' => 'low']);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('filterPriority', 'urgent')
        ->assertSee('Urgent One')
        ->assertDontSee('Low One');
});

test('user can search tasks by title', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Alpha Task']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Beta Task']);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('search', 'Alpha')
        ->assertSee('Alpha Task')
        ->assertDontSee('Beta Task');
});

test('user can clear task filters', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('search', 'something')
        ->set('filterStatus', 'pending')
        ->set('filterPriority', 'high')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('filterStatus', '')
        ->assertSet('filterPriority', '');
});

// ==========================================
// RELATIONSHIP/SCOPE TESTS
// ==========================================

test('task belongs to project and user', function () {
    $task = Task::factory()->create();

    expect($task->project)->toBeInstanceOf(Project::class);
    expect($task->user)->toBeInstanceOf(User::class);
});

test('task scopes work correctly', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Task::factory()->pending()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->inProgress()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->completed()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    expect(Task::pending()->count())->toBe(1);
    expect(Task::inProgress()->count())->toBe(1);
    expect(Task::completed()->count())->toBe(1);
});

test('task overdue scope works', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Task::factory()->overdue()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->pending()->create(['project_id' => $project->id, 'user_id' => $user->id, 'due_date' => now()->addDays(5)]);

    expect(Task::overdue()->count())->toBe(1);
});

// ==========================================
// MODAL TESTS
// ==========================================

test('open modal resets task form', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('title', 'leftover')
        ->call('openModal')
        ->assertSet('title', '')
        ->assertSet('showModal', true)
        ->assertSet('isEditing', false);
});

test('user can filter tasks by tag', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create(['name' => 'urgent-tag']);
    $task1 = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Tagged One']);
    $task1->tags()->attach($tag);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Untagged One']);

    Livewire::actingAs($user)
        ->test(TaskManager::class, ['project' => $project])
        ->set('filterTag', $tag->id)
        ->assertSee('Tagged One')
        ->assertDontSee('Untagged One');
});

test('task byPriority scope works', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'priority' => 'urgent']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'priority' => 'low']);

    expect(Task::byPriority('urgent')->count())->toBe(1);
    expect(Task::byPriority('low')->count())->toBe(1);
});

test('user has projects and tasks relationships', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    expect($user->projects)->toHaveCount(1);
    expect($user->tasks)->toHaveCount(1);
    expect($user->projects->first())->toBeInstanceOf(Project::class);
    expect($user->tasks->first())->toBeInstanceOf(Task::class);
});

test('tag has tasks relationship', function () {
    $tag = Tag::factory()->create();
    $task = Task::factory()->create();
    $task->tags()->attach($tag);

    expect($tag->fresh()->tasks)->toHaveCount(1);
});
