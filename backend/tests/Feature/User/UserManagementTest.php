<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function userToken(User $user): string
{
    return (string) JWTAuth::fromUser($user);
}

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

it('creates a user as admin', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.users@example.com',
    ]);

    $response = postJson('/api/users', [
        'name' => 'Created User',
        'email' => 'created.user@example.com',
        'password' => 'Secure@123',
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User created successfully.')
        ->assertJsonPath('data.user.email', 'created.user@example.com')
        ->assertJsonPath('data.user.role.name', 'employee');

    assertDatabaseHas('users', [
        'email' => 'created.user@example.com',
        'status' => 'active',
    ]);
});

it('creates a user as admin using role name', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.users.byname@example.com',
    ]);

    $response = postJson('/api/users', [
        'name' => 'Role Name User',
        'email' => 'role.name.user@example.com',
        'password' => 'Secure@123',
        'role' => 'manager',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'role.name.user@example.com')
        ->assertJsonPath('data.user.role.name', 'manager');
});

it('creates a user as admin with default employee role when role is not provided', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.users.defaultrole@example.com',
    ]);

    $response = postJson('/api/users', [
        'name' => 'Default Role User',
        'email' => 'default.role.user@example.com',
        'password' => 'Secure@123',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.role.name', 'employee');
});

it('fails to create a user as non admin', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employee = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'non.admin@example.com',
    ]);

    $response = postJson('/api/users', [
        'name' => 'Forbidden User',
        'email' => 'forbidden.user@example.com',
        'password' => 'Secure@123',
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.userToken($employee),
    ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('updates a user as admin', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.update@example.com',
    ]);

    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'target.update@example.com',
    ]);

    $response = putJson('/api/users/'.$user->id, [
        'name' => 'Updated Name',
        'email' => 'updated.user@example.com',
        'password' => 'Updated@123',
        'role_id' => $managerRole->id,
        'status' => 'inactive',
    ], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User updated successfully.')
        ->assertJsonPath('data.user.email', 'updated.user@example.com')
        ->assertJsonPath('data.user.status', 'inactive')
        ->assertJsonPath('data.user.role.name', 'manager');

    $freshUser = User::query()->findOrFail($user->id);
    expect(Hash::check('Updated@123', (string) $freshUser->password))->toBeTrue();
});

it('updates a user role using role name and prioritizes role over role_id', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.update.byname@example.com',
    ]);

    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'target.update.byname@example.com',
    ]);

    $response = putJson('/api/users/'.$user->id, [
        'role' => 'manager',
        'role_id' => $employeeRole->id,
    ], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.role.name', 'manager');

    $freshUser = User::query()->with('role')->findOrFail($user->id);
    expect($freshUser->role?->id)->toBe($managerRole->id);
});

it('deletes a user as admin', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.delete@example.com',
    ]);

    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'target.delete@example.com',
    ]);

    $response = deleteJson('/api/users/'.$user->id, [], [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User deleted successfully.');

    $deletedUser = User::query()->withTrashed()->find($user->id);

    expect($deletedUser)->not->toBeNull();
    expect($deletedUser?->deleted_at)->not->toBeNull();
});

it('forbids non admin users from updating users', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $actor = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.update.forbidden@example.com',
    ]);

    $target = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'target.update.forbidden@example.com',
    ]);

    putJson('/api/users/'.$target->id, [
        'name' => 'Should Not Update',
    ], [
        'Authorization' => 'Bearer '.userToken($actor),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('forbids non admin users from deleting users', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $actor = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.delete.forbidden@example.com',
    ]);

    $target = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'target.delete.forbidden@example.com',
    ]);

    deleteJson('/api/users/'.$target->id, [], [
        'Authorization' => 'Bearer '.userToken($actor),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('lists users with search and filters using pagination', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'name' => 'Admin Lister',
        'email' => 'admin.list@example.com',
    ]);

    User::factory()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Alpha Target',
        'email' => 'alpha.target@example.com',
        'status' => 'active',
    ]);

    User::factory()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Beta Not Match',
        'email' => 'beta.notmatch@example.com',
        'status' => 'inactive',
    ]);

    User::factory()->create([
        'role_id' => $managerRole->id,
        'name' => 'Alpha Manager',
        'email' => 'alpha.manager@example.com',
        'status' => 'active',
    ]);

    $response = getJson('/api/users?search=alpha&role_id='.$employeeRole->id.'&status=active&per_page=5', [
        'Authorization' => 'Bearer '.userToken($admin),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Users fetched successfully.')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonCount(1, 'data.users')
        ->assertJsonPath('data.users.0.email', 'alpha.target@example.com');
});

it('forbids employee from listing users', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.read.forbidden@example.com',
    ]);

    getJson('/api/users', [
        'Authorization' => 'Bearer '.userToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('errors', []);
});

it('forbids employee from fetching user by id', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.self@example.com',
    ]);

    $otherUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.other@example.com',
    ]);

    getJson('/api/users/'.$employeeUser->id, [
        'Authorization' => 'Bearer '.userToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    getJson('/api/users/'.$otherUser->id, [
        'Authorization' => 'Bearer '.userToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('forbids manager from listing users', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.scope@example.com',
    ]);

    getJson('/api/users', [
        'Authorization' => 'Bearer '.userToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('forbids manager from fetching user by id', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.show@example.com',
    ]);

    $differentDepartmentUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'different.department.show@example.com',
    ]);

    getJson('/api/users/'.$differentDepartmentUser->id, [
        'Authorization' => 'Bearer '.userToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('blocks unauthorized access to user module endpoints', function (): void {
    getJson('/api/users')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_UNAUTHORIZED');
});
