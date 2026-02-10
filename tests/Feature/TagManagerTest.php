<?php

use App\Livewire\TagManager;
use App\Models\Tag;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

// ==========================================
// ACCESS TESTS
// ==========================================

test('guest is redirected from tags page', function () {
    $this->get('/tags')->assertRedirect('/login');
});

test('regular member cannot access tags page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/tags')->assertForbidden();
});

test('admin can access tags page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/tags')->assertOk();
});

// ==========================================
// CREATE TESTS
// ==========================================

test('admin can create a tag', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('openModal')
        ->set('name', 'bug')
        ->set('color', '#EF4444')
        ->call('save');

    $this->assertDatabaseHas('tags', ['name' => 'bug', 'color' => '#EF4444']);
});

test('tag name is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('openModal')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('tag name must be unique', function () {
    $admin = User::factory()->admin()->create();
    Tag::factory()->create(['name' => 'existing']);

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('openModal')
        ->set('name', 'existing')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('tag color must be valid hex', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('openModal')
        ->set('name', 'test')
        ->set('color', 'not-a-color')
        ->call('save')
        ->assertHasErrors(['color']);
});

// ==========================================
// EDIT TESTS
// ==========================================

test('admin can edit a tag', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create(['name' => 'old-name']);

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('edit', $tag->id)
        ->set('name', 'new-name')
        ->call('save');

    $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'new-name']);
});

// ==========================================
// DELETE TESTS
// ==========================================

test('admin can delete a tag', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('confirmDelete', $tag->id)
        ->call('delete');

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('admin can cancel delete', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->call('confirmDelete', $tag->id)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false);

    $this->assertDatabaseHas('tags', ['id' => $tag->id]);
});

// ==========================================
// SEARCH TESTS
// ==========================================

test('admin can search tags', function () {
    $admin = User::factory()->admin()->create();
    Tag::factory()->create(['name' => 'bug']);
    Tag::factory()->create(['name' => 'feature']);

    Livewire::actingAs($admin)
        ->test(TagManager::class)
        ->set('search', 'bug')
        ->assertSee('bug')
        ->assertDontSee('feature');
});

// ==========================================
// API TESTS
// ==========================================

test('any authenticated user can list tags via api', function () {
    $user = User::factory()->create();
    Tag::factory()->count(3)->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/tags')->assertOk()->assertJsonCount(3);
});

test('member cannot create tags via api', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/tags', ['name' => 'test'])->assertForbidden();
});

test('admin can create tags via api', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/tags', ['name' => 'urgent', 'color' => '#EF4444'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'urgent');
});

test('admin can update tags via api', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();
    Sanctum::actingAs($admin);

    $this->putJson('/api/tags/' . $tag->id, ['name' => 'updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'updated');
});

test('admin can delete tags via api', function () {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();
    Sanctum::actingAs($admin);

    $this->deleteJson('/api/tags/' . $tag->id)->assertOk();
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('any authenticated user can view single tag via api', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'my-tag']);
    Sanctum::actingAs($user);

    $this->getJson('/api/tags/' . $tag->id)
        ->assertOk()
        ->assertJsonPath('name', 'my-tag');
});
