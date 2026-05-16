<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function departmentUserToken(User $user): string
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

it('admin can create department', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.create@example.com',
    ]);

    $manager = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.department.create@example.com',
    ]);

    $response = postJson('/api/departments', [
        'name' => 'Engineering',
        'manager_id' => $manager->id,
    ], [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Department created successfully.')
        ->assertJsonPath('data.department.name', 'Engineering')
        ->assertJsonPath('data.department.manager_id', $manager->id)
        ->assertJsonPath('data.department.manager.role.name', 'manager');

    assertDatabaseHas('departments', [
        'name' => 'Engineering',
        'manager_id' => $manager->id,
        'status' => 'active',
    ]);
});

it('non admin cannot create department', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employee = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.department.create.forbidden@example.com',
    ]);

    postJson('/api/departments', [
        'name' => 'Finance',
    ], [
        'Authorization' => 'Bearer '.departmentUserToken($employee),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('admin can list departments with pagination', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.list@example.com',
    ]);

    Department::query()->create([
        'name' => 'Engineering',
        'status' => 'active',
    ]);

    Department::query()->create([
        'name' => 'Finance',
        'status' => 'active',
    ]);

    getJson('/api/departments?per_page=1', [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Departments fetched successfully.')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonCount(1, 'data.departments');
});

it('admin can search departments by name', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.search@example.com',
    ]);

    Department::query()->create([
        'name' => 'Engineering',
        'status' => 'active',
    ]);

    Department::query()->create([
        'name' => 'Finance',
        'status' => 'active',
    ]);

    getJson('/api/departments?search=eng', [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.departments.0.name', 'Engineering');
});

it('non admin gets forbidden on departments endpoints', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employee = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.department.forbidden.list@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Operations',
        'status' => 'active',
    ]);

    getJson('/api/departments', [
        'Authorization' => 'Bearer '.departmentUserToken($employee),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('errors', []);

    getJson('/api/departments/'.$department->id, [
        'Authorization' => 'Bearer '.departmentUserToken($employee),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('errors', []);
});

it('admin can get one department', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.show@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Product',
        'status' => 'active',
    ]);

    getJson('/api/departments/'.$department->id, [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Department fetched successfully.')
        ->assertJsonPath('data.department.id', $department->id)
        ->assertJsonPath('data.department.name', 'Product');
});

it('admin can update department', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.update@example.com',
    ]);

    $manager = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.department.update@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Old Department',
        'status' => 'active',
    ]);

    putJson('/api/departments/'.$department->id, [
        'name' => 'Updated Department',
        'manager_id' => $manager->id,
    ], [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Department updated successfully.')
        ->assertJsonPath('data.department.name', 'Updated Department')
        ->assertJsonPath('data.department.manager_id', $manager->id)
        ->assertJsonPath('data.department.manager.role.name', 'manager');

    assertDatabaseHas('departments', [
        'id' => $department->id,
        'name' => 'Updated Department',
        'manager_id' => $manager->id,
    ]);
});

it('admin can delete department', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.delete@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Temp Department',
        'status' => 'active',
    ]);

    deleteJson('/api/departments/'.$department->id, [], [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Department deleted successfully.');

    $deletedDepartment = Department::query()->withTrashed()->find($department->id);

    expect($deletedDepartment)->not->toBeNull();
    expect($deletedDepartment?->deleted_at)->not->toBeNull();
});

it('returns validation errors for invalid department payload', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.validation@example.com',
    ]);

    postJson('/api/departments', [], [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['name'],
        ]);
});

it('validates manager_id must belong to manager role', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.manager.validation@example.com',
    ]);

    $employee = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.department.manager.validation@example.com',
    ]);

    postJson('/api/departments', [
        'name' => 'Security',
        'manager_id' => $employee->id,
    ], [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['manager_id'],
        ]);
});

it('returns not found for missing department', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.department.notfound@example.com',
    ]);

    getJson('/api/departments/00000000-0000-0000-0000-000000000000', [
        'Authorization' => 'Bearer '.departmentUserToken($admin),
    ])->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Department not found.')
        ->assertJsonPath('code', 'DEPARTMENT_NOT_FOUND');
});
