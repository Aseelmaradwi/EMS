<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->firstOrCreate(
        ['name' => 'admin'],
        ['description' => 'System administrator role']
    );

    Role::query()->firstOrCreate(
        ['name' => 'manager'],
        ['description' => 'Department manager role']
    );

    Role::query()->firstOrCreate(
        ['name' => 'employee'],
        ['description' => 'Standard employee role']
    );
});

it('registers a user successfully', function (): void {
    $response = postJson('/api/auth/register', [
        'name' => 'Alice Worker',
        'email' => 'alice@example.com',
        'password' => 'Secure@123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User registered successfully.')
        ->assertJsonPath('data.user.email', 'alice@example.com')
        ->assertJsonPath('data.user.role.name', 'employee');

    assertDatabaseHas('users', [
        'email' => 'alice@example.com',
        'status' => 'active',
    ]);
});

it('ignores role and role_id input during registration and always assigns employee role', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $response = postJson('/api/auth/register', [
        'name' => 'Bypass Attempt',
        'email' => 'bypass@example.com',
        'password' => 'Secure@123',
        'role' => 'admin',
        'role_id' => $adminRole->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'bypass@example.com')
        ->assertJsonPath('data.user.role.name', 'employee');
});

it('logs in successfully with valid credentials', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    User::query()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Bob Worker',
        'email' => 'bob@example.com',
        'password' => 'Secure@123',
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'bob@example.com',
        'password' => 'Secure@123',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['user', 'token'],
            'meta' => ['token_type', 'expires_in'],
        ]);
});

it('logs in manager successfully when account is active', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    User::query()->create([
        'role_id' => $managerRole->id,
        'name' => 'Manager User',
        'email' => 'manager.login@example.com',
        'password' => 'password',
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'manager.login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'manager.login@example.com')
        ->assertJsonPath('data.user.role.name', 'manager');
});

it('fails login with invalid credentials', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    User::query()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Charlie Worker',
        'email' => 'charlie@example.com',
        'password' => 'Secure@123',
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'charlie@example.com',
        'password' => 'Wrong@123',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_INVALID_CREDENTIALS');
});

it('denies unauthorized access to me endpoint', function (): void {
    $response = getJson('/api/auth/me');

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_UNAUTHORIZED');
});

it('validates token access on protected endpoint', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    User::query()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Dina Worker',
        'email' => 'dina@example.com',
        'password' => 'Secure@123',
        'status' => 'active',
    ]);

    $loginResponse = postJson('/api/auth/login', [
        'email' => 'dina@example.com',
        'password' => 'Secure@123',
    ]);

    $token = (string) $loginResponse->json('data.token');

    getJson('/api/auth/me', [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('success', true);

    postJson('/api/auth/logout', [], [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logged out successfully');

    getJson('/api/auth/me', [
        'Authorization' => 'Bearer '.$token,
    ])->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_UNAUTHORIZED');
});

it('returns 401 for logout when token is invalid', function (): void {
    postJson('/api/auth/logout', [], [
        'Authorization' => 'Bearer invalid.token.value',
    ])->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_UNAUTHORIZED');
});
