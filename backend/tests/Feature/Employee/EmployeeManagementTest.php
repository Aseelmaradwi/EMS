<?php

use App\Models\Department;
use App\Models\Employee;
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

function employeeUserToken(User $user): string
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

it('admin can create employee', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.create@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'profile.employee.create@example.com',
        'name' => 'Employee Person',
    ]);

    $department = Department::query()->create([
        'name' => 'Engineering',
        'status' => 'active',
    ]);

    $response = postJson('/api/employees', [
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'phone' => '0123456789',
        'address' => 'Main street',
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee created successfully.')
        ->assertJsonPath('data.employee.user_id', $employeeUser->id)
        ->assertJsonPath('data.employee.department_id', $department->id)
        ->assertJsonPath('data.employee.phone', '0123456789')
        ->assertJsonPath('data.employee.address', 'Main street')
        ->assertJsonPath('data.employee.user.role.name', 'employee');

    assertDatabaseHas('employees', [
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'phone' => '0123456789',
        'address' => 'Main street',
    ]);
});

it('non admin users are forbidden from employee endpoints', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeActor = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.actor.forbidden@example.com',
    ]);

    getJson('/api/employees', [
        'Authorization' => 'Bearer '.employeeUserToken($employeeActor),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('errors', []);
});

it('returns validation error for invalid user_id', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.invalid.userid@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Finance',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => '00000000-0000-0000-0000-000000000000',
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['user_id'],
        ]);
});

it('returns validation error for invalid department_id', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.invalid.departmentid@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.for.invalid.departmentid@example.com',
    ]);

    postJson('/api/employees', [
        'user_id' => $employeeUser->id,
        'department_id' => '00000000-0000-0000-0000-000000000000',
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['department_id'],
        ]);
});

it('admin can create manager user as employee profile', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.role.validation@example.com',
    ]);

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.employee.profile.allowed@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Operations',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => $managerUser->id,
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.employee.user.role.name', 'manager');
});

it('admin role user cannot be created as employee profile', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $adminActor = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.actor.employee.profile.disallowed@example.com',
    ]);

    $adminTarget = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.target.employee.profile.disallowed@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Admin Exclusion Department',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => $adminTarget->id,
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($adminActor),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['user_id'],
        ]);
});

it('prevents creating duplicate employee profile for the same user', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.duplicate@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.duplicate.target@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Support',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertCreated();

    postJson('/api/employees', [
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Employee profile already exists for this user.')
        ->assertJsonPath('code', 'EMPLOYEE_ALREADY_EXISTS')
        ->assertJsonPath('errors', []);
});

it('returns validation error for malformed user_id uuid', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.invalid.uuid.userid@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Legal',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => 'not-a-uuid',
        'department_id' => $department->id,
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['user_id'],
        ]);
});

it('admin can list employees with pagination', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.list@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Product',
        'status' => 'active',
    ]);

    $employeeUserA = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.list.a@example.com',
        'name' => 'Employee A',
    ]);

    $employeeUserB = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.list.b@example.com',
        'name' => 'Employee B',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LISTA-001',
        'first_name' => 'Employee',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
        'phone' => '111111111',
        'address' => 'Address A',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LISTB-001',
        'first_name' => 'Employee',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
        'phone' => '222222222',
        'address' => 'Address B',
    ]);

    getJson('/api/employees?per_page=1', [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employees fetched successfully.')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonCount(1, 'data.employees');
});

it('admin can filter employees by department and search', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.filter@example.com',
    ]);

    $departmentEngineering = Department::query()->create([
        'name' => 'Engineering',
        'status' => 'active',
    ]);

    $departmentFinance = Department::query()->create([
        'name' => 'Finance',
        'status' => 'active',
    ]);

    $aliceUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Alice Carson',
        'email' => 'alice.carson@example.com',
    ]);

    $bobUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Bob Rivers',
        'email' => 'bob.rivers@example.com',
    ]);

    Employee::query()->create([
        'user_id' => $aliceUser->id,
        'department_id' => $departmentEngineering->id,
        'employee_code' => 'EMP-FLTR-001',
        'first_name' => 'Alice',
        'last_name' => 'Carson',
        'email' => $aliceUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $bobUser->id,
        'department_id' => $departmentFinance->id,
        'employee_code' => 'EMP-FLTR-002',
        'first_name' => 'Bob',
        'last_name' => 'Rivers',
        'email' => $bobUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    getJson('/api/employees?department_id='.$departmentEngineering->id.'&search=alice', [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.employees.0.user.email', 'alice.carson@example.com');
});

it('admin can filter employees by name and role', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.name.role.filter@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Name Role Filter Department',
        'status' => 'active',
    ]);

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'name' => 'Mona Manager',
        'email' => 'mona.manager@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Mona Employee',
        'email' => 'mona.employee@example.com',
    ]);

    Employee::query()->create([
        'user_id' => $managerUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-NR-001',
        'first_name' => 'Mona',
        'last_name' => 'Manager',
        'email' => $managerUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-NR-002',
        'first_name' => 'Mona',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    getJson('/api/employees?name=mona&role=manager', [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.employees.0.user.role.name', 'manager')
        ->assertJsonPath('data.employees.0.user.email', 'mona.manager@example.com');
});

it('admin can show employee by id', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.show@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.show.target@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Security',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SHOW-001',
        'first_name' => 'Target',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
        'phone' => '333333333',
        'address' => 'Show Address',
    ]);

    getJson('/api/employees/'.$employee->id, [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee fetched successfully.')
        ->assertJsonPath('data.employee.id', $employee->id)
        ->assertJsonPath('data.employee.user_id', $employeeUser->id);
});

it('admin can update employee', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.update@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.update.target@example.com',
    ]);

    $departmentA = Department::query()->create([
        'name' => 'QA',
        'status' => 'active',
    ]);

    $departmentB = Department::query()->create([
        'name' => 'Research',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $departmentA->id,
        'employee_code' => 'EMP-UPD-001',
        'first_name' => 'Update',
        'last_name' => 'Target',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
        'phone' => '444444444',
        'address' => 'Old Address',
    ]);

    putJson('/api/employees/'.$employee->id, [
        'department_id' => $departmentB->id,
        'phone' => '999999999',
        'address' => 'New Address',
    ], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee updated successfully.')
        ->assertJsonPath('data.employee.department_id', $departmentB->id)
        ->assertJsonPath('data.employee.phone', '999999999')
        ->assertJsonPath('data.employee.address', 'New Address');

    assertDatabaseHas('employees', [
        'id' => $employee->id,
        'department_id' => $departmentB->id,
        'phone' => '999999999',
        'address' => 'New Address',
    ]);
});

it('admin can delete employee', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.delete@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.delete.target@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Marketing',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-DEL-001',
        'first_name' => 'Delete',
        'last_name' => 'Target',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
        'phone' => '555555555',
        'address' => 'Delete Address',
    ]);

    deleteJson('/api/employees/'.$employee->id, [], [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee deleted successfully.');

    $deletedEmployee = Employee::query()->withTrashed()->find($employee->id);

    expect($deletedEmployee)->not->toBeNull();
    expect($deletedEmployee?->deleted_at)->not->toBeNull();
});

it('returns not found for missing employee', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.employee.notfound@example.com',
    ]);

    getJson('/api/employees/00000000-0000-0000-0000-000000000000', [
        'Authorization' => 'Bearer '.employeeUserToken($admin),
    ])->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Employee not found.')
        ->assertJsonPath('code', 'EMPLOYEE_NOT_FOUND');
});
