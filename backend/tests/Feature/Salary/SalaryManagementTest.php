<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function salaryToken(User $user): string
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

it('admin can create salary', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.create@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.salary.create@example.com',
        'name' => 'Salary Employee',
    ]);

    $department = Department::query()->create([
        'name' => 'Payroll Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-001',
        'first_name' => 'Salary',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 5000,
        'bonus' => 300,
        'deductions' => 100,
    ], [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Salary created successfully.')
        ->assertJsonPath('data.salary.employee_id', $employee->id)
        ->assertJsonPath('data.salary.base_salary', 5000)
        ->assertJsonPath('data.salary.amount', 5000)
        ->assertJsonPath('data.salary.bonus', 300)
        ->assertJsonPath('data.salary.deduction', 100)
        ->assertJsonPath('data.salary.deductions', 100)
        ->assertJsonPath('data.salary.net_salary', 5200);

    assertDatabaseHas('salaries', [
        'employee_id' => $employee->id,
        'base_salary' => 5000,
        'bonus' => 300,
        'deduction' => 100,
        'created_by' => $admin->id,
    ]);
});

it('admin can assign salary to manager', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.manager@example.com',
    ]);

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.salary.target@example.com',
        'name' => 'Manager Salary',
    ]);

    $department = Department::query()->create([
        'name' => 'Management',
        'status' => 'active',
    ]);

    $managerEmployee = Employee::query()->create([
        'user_id' => $managerUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-MGR-001',
        'first_name' => 'Manager',
        'last_name' => 'Salary',
        'email' => $managerUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/salaries', [
        'employee_id' => $managerEmployee->id,
        'amount' => 7000,
        'bonus' => 400,
        'deductions' => 250,
    ], [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.salary.employee.user.role.name', 'manager')
        ->assertJsonPath('data.salary.net_salary', 7150);
});

it('manager cannot access salary endpoints', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.forbidden.manager@example.com',
    ]);

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.salary.forbidden@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Manager Salary Department',
        'status' => 'active',
    ]);

    $managerEmployee = Employee::query()->create([
        'user_id' => $managerUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-MGR-002',
        'first_name' => 'Manager',
        'last_name' => 'Forbidden',
        'email' => $managerUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $salary = Salary::query()->create([
        'employee_id' => $managerEmployee->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 4000,
        'bonus' => 100,
        'deduction' => 50,
        'net_salary' => 4050,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/salaries', [
        'Authorization' => 'Bearer '.salaryToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    getJson('/api/salaries/'.$salary->id, [
        'Authorization' => 'Bearer '.salaryToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    postJson('/api/salaries', [
        'employee_id' => $managerEmployee->id,
        'amount' => 4200,
    ], [
        'Authorization' => 'Bearer '.salaryToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    putJson('/api/salaries/'.$salary->id, [
        'bonus' => 300,
    ], [
        'Authorization' => 'Bearer '.salaryToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    deleteJson('/api/salaries/'.$salary->id, [], [
        'Authorization' => 'Bearer '.salaryToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('employee cannot access salary endpoints', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.forbidden.employee@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.salary.forbidden@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Employee Salary Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-EMP-001',
        'first_name' => 'Employee',
        'last_name' => 'Forbidden',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $salary = Salary::query()->create([
        'employee_id' => $employee->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 3500,
        'bonus' => 200,
        'deduction' => 75,
        'net_salary' => 3625,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/salaries', [
        'Authorization' => 'Bearer '.salaryToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    getJson('/api/salaries/'.$salary->id, [
        'Authorization' => 'Bearer '.salaryToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 3800,
    ], [
        'Authorization' => 'Bearer '.salaryToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    putJson('/api/salaries/'.$salary->id, [
        'bonus' => 150,
    ], [
        'Authorization' => 'Bearer '.salaryToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');

    deleteJson('/api/salaries/'.$salary->id, [], [
        'Authorization' => 'Bearer '.salaryToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('returns duplicate salary conflict for the same employee', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.duplicate@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.salary.duplicate@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Duplicate Salary Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-DUP-001',
        'first_name' => 'Duplicate',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 4500,
        'bonus' => 200,
        'deductions' => 90,
    ], [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertCreated();

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 4700,
        'bonus' => 300,
        'deductions' => 120,
    ], [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Salary already exists for this employee.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'SALARY_ALREADY_EXISTS');
});

it('returns net_salary from salary model accessor', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.net.example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.salary.net.example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Net Salary Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-NET-001',
        'first_name' => 'Net',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $salary = Salary::query()->create([
        'employee_id' => $employee->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 6000,
        'bonus' => 500,
        'deduction' => 350,
        'net_salary' => 6150,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/salaries/'.$salary->id, [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.salary.base_salary', 6000)
        ->assertJsonPath('data.salary.amount', 6000)
        ->assertJsonPath('data.salary.bonus', 500)
        ->assertJsonPath('data.salary.deduction', 350)
        ->assertJsonPath('data.salary.deductions', 350)
        ->assertJsonPath('data.salary.net_salary', 6150);
});

it('filters salaries by month', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'email' => 'admin.salary.month.filter@example.com',
    ]);

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.salary.month.filter@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Salary Month Filter Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-SAL-MONTH-001',
        'first_name' => 'Month',
        'last_name' => 'Filter',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $matchingSalary = Salary::query()->create([
        'employee_id' => $employee->id,
        'effective_from' => '2026-03-01',
        'base_salary' => 5100,
        'bonus' => 250,
        'deduction' => 100,
        'net_salary' => 5250,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    Salary::query()->create([
        'employee_id' => $employee->id,
        'effective_from' => '2026-04-01',
        'base_salary' => 5200,
        'bonus' => 250,
        'deduction' => 100,
        'net_salary' => 5350,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/salaries?month=2026-03', [
        'Authorization' => 'Bearer '.salaryToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.salaries')
        ->assertJsonPath('data.salaries.0.id', $matchingSalary->id)
        ->assertJsonPath('data.salaries.0.base_salary', 5100)
        ->assertJsonPath('data.salaries.0.net_salary', 5250);
});
