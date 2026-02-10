<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

// ==========================================
// REGISTER TESTS
// ==========================================

test('user can register via api', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('register requires name', function () {
    $this->postJson('/api/register', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['name']);
});

test('register requires email', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('register requires valid email', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('register requires unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('register requires password', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

test('register requires password min 8 characters', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

test('register requires password confirmation', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

test('register returns token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    expect($response->json('token'))->not->toBeNull();
});

// ==========================================
// LOGIN TESTS
// ==========================================

test('user can login via api', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

test('login requires email', function () {
    $this->postJson('/api/login', [
        'password' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('login requires password', function () {
    $this->postJson('/api/login', [
        'email' => 'user@example.com',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('login fails with non-existent user', function () {
    $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('login returns token', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk();
    expect($response->json('token'))->not->toBeNull();
});

// ==========================================
// LOGOUT TESTS
// ==========================================

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/logout')
        ->assertOk()
        ->assertJson(['message' => 'Logged out successfully']);
});

test('unauthenticated user cannot logout', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});

// ==========================================
// USER TESTS
// ==========================================

test('authenticated user can get their info', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    Sanctum::actingAs($user);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
});

test('unauthenticated user cannot get user info', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('user endpoint returns correct user data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'email', 'role', 'created_at', 'updated_at']);
});
