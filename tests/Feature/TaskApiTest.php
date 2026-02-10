<?php

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ==========================================
// AUTH TESTS
// ==========================================

test('unauthenticated user cannot access tasks api', function () {
    $project = Project::factory()->create();

    $this->getJson('/api/projects/' . $project->id . '/tasks')->assertUnauthorized();
});

// ==========================================
// INDEX TESTS
// ==========================================

test('user can list tasks for their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->count(3)->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id . '/tasks')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user cannot list tasks for other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $otherProject->id . '/tasks')
        ->assertForbidden();
});

// ==========================================
// STORE TESTS
// ==========================================

test('user can create a task via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->postJson('/api/projects/' . $project->id . '/tasks', [
        'title' => 'New Task',
        'priority' => 'high',
    ])->assertCreated()
        ->assertJsonPath('data.title', 'New Task');
});

test('user can create a task with tags via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/projects/' . $project->id . '/tasks', [
        'title' => 'Tagged Task',
        'tag_ids' => [$tag->id],
    ]);

    $response->assertCreated();
    expect(Task::first()->tags)->toHaveCount(1);
});

test('create task requires title', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->postJson('/api/projects/' . $project->id . '/tasks', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('create task validates status', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->postJson('/api/projects/' . $project->id . '/tasks', [
        'title' => 'Test',
        'status' => 'invalid',
    ])->assertStatus(422)->assertJsonValidationErrors(['status']);
});

// ==========================================
// SHOW TESTS
// ==========================================

test('user can view their task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id . '/tasks/' . $task->id)
        ->assertOk()
        ->assertJsonPath('title', $task->title);
});

// ==========================================
// UPDATE TESTS
// ==========================================

test('user can update their task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->putJson('/api/projects/' . $project->id . '/tasks/' . $task->id, [
        'title' => 'Updated Title',
        'status' => 'completed',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');
});

test('user can update task tags via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    $tag = Tag::factory()->create();

    Sanctum::actingAs($user);

    $this->putJson('/api/projects/' . $project->id . '/tasks/' . $task->id, [
        'tag_ids' => [$tag->id],
    ])->assertOk();

    expect($task->fresh()->tags)->toHaveCount(1);
});

// ==========================================
// DELETE TESTS
// ==========================================

test('user can delete their task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->deleteJson('/api/projects/' . $project->id . '/tasks/' . $task->id)->assertOk();
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('user cannot delete task in other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $otherProject->id, 'user_id' => $otherProject->user_id]);

    Sanctum::actingAs($user);

    $this->deleteJson('/api/projects/' . $otherProject->id . '/tasks/' . $task->id)
        ->assertForbidden();
});

// ==========================================
// FILTER TESTS (API)
// ==========================================

test('user can search tasks via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Alpha Task']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'title' => 'Beta Task']);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id . '/tasks?search=Alpha')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('user can filter tasks by status via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->pending()->create(['project_id' => $project->id, 'user_id' => $user->id]);
    Task::factory()->completed()->create(['project_id' => $project->id, 'user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id . '/tasks?status=pending')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('user can filter tasks by priority via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'priority' => 'urgent']);
    Task::factory()->create(['project_id' => $project->id, 'user_id' => $user->id, 'priority' => 'low']);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $project->id . '/tasks?priority=urgent')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('user cannot create task in other users project via api', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/projects/' . $otherProject->id . '/tasks', [
        'title' => 'Sneaky Task',
    ])->assertForbidden();
});

test('user cannot view task from other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $otherProject->id, 'user_id' => $otherProject->user_id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/projects/' . $otherProject->id . '/tasks/' . $task->id)
        ->assertForbidden();
});

test('user cannot update task in other users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $otherProject->id, 'user_id' => $otherProject->user_id]);

    Sanctum::actingAs($user);

    $this->putJson('/api/projects/' . $otherProject->id . '/tasks/' . $task->id, [
        'title' => 'Hacked',
    ])->assertForbidden();
});
