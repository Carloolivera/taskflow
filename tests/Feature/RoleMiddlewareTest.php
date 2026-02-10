<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ==========================================
// USER MODEL TESTS
// ==========================================

test('user isAdmin returns true for admin role', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->isAdmin())->toBeTrue();
});

test('user isAdmin returns false for member role', function () {
    $user = User::factory()->create();

    expect($user->isAdmin())->toBeFalse();
});

test('user factory defaults to member role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe('member');
});

test('user factory admin state sets admin role', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe('admin');
});

// ==========================================
// WEB ROUTE PROTECTION TESTS
// ==========================================

test('regular user can still access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

// ==========================================
// MIDDLEWARE JSON RESPONSE TEST
// ==========================================

test('admin middleware returns json 403 for api requests', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/test-admin');

    $response->assertForbidden()
        ->assertJson(['message' => 'Forbidden. Admin access required.']);
});

test('admin middleware allows admin for api requests', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/test-admin');

    $response->assertOk();
});

test('admin middleware returns 403 for web requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/tags')
        ->assertForbidden();
});
