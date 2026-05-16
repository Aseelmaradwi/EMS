<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

function reportingToken(User $user): string
{
    return (string) JWTAuth::fromUser($user);
}

function createReportEmployee(User $user, Department $department, string $code): Employee
{
    return Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => $code,
        'first_name' => 'Report',
        'last_name' => 'Employee',
        'email' => $user->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);
}

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'System administrator role']);
    Role::query()->firstOrCreate(['name' => 'manager'], ['description' => 'Department manager role']);
    Role::query()->firstOrCreate(['name' => 'employee'], ['description' => 'Standard employee role']);
});

it('admin can access employees report', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Employees Department', 'status' => 'active']);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    createReportEmployee($employeeUserA, $department, 'EMP-RPT-001');
    createReportEmployee($employeeUserB, $department, 'EMP-RPT-002');

    getJson('/api/reports/employees', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Report fetched successfully')
        ->assertJsonPath('data.total_employees', 2);
});

it('admin can access departments report', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Department::query()->create(['name' => 'Report Department A', 'status' => 'active']);
    Department::query()->create(['name' => 'Report Department B', 'status' => 'active']);

    getJson('/api/reports/departments', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Report fetched successfully')
        ->assertJsonPath('data.total_departments', 2);
});

it('admin can access attendance report', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Attendance Department', 'status' => 'active']);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
    $employee = createReportEmployee($employeeUser, $department, 'EMP-RPT-003');

    Attendance::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2030-01-10',
        'check_in_time' => '2030-01-10 08:00:00',
        'check_out_time' => '2030-01-10 16:00:00',
        'status' => 'present',
    ]);

    getJson('/api/reports/attendance?from_date=2030-01-01&to_date=2030-01-31', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Report fetched successfully')
        ->assertJsonPath('data.total_present', 1);
});

it('admin can access salaries report', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Salaries Department', 'status' => 'active']);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
    $employee = createReportEmployee($employeeUser, $department, 'EMP-RPT-004');

    Salary::query()->create([
        'employee_id' => $employee->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 1000,
        'bonus' => 100,
        'deduction' => 50,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/reports/salaries', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Report fetched successfully')
        ->assertJsonPath('data.total_salary_cost', 1050);
});

it('admin can access leaves report', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Leaves Department', 'status' => 'active']);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
    $employee = createReportEmployee($employeeUser, $department, 'EMP-RPT-005');

    LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Approved leave',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'approved',
    ]);

    getJson('/api/reports/leaves', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Report fetched successfully')
        ->assertJsonPath('data.total_leaves', 1)
        ->assertJsonPath('data.approved_leaves', 1);
});

it('manager is forbidden from reports', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $manager = User::factory()->create(['role_id' => $managerRole->id]);

    getJson('/api/reports/employees', [
        'Authorization' => 'Bearer '.reportingToken($manager),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('employee is forbidden from reports', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    getJson('/api/reports/employees', [
        'Authorization' => 'Bearer '.reportingToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden. Admin access is required.')
        ->assertJsonPath('errors', [])
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('attendance report computes absent correctly', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Attendance Absent Department', 'status' => 'active']);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserC = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = createReportEmployee($employeeUserA, $department, 'EMP-RPT-006');
    $employeeB = createReportEmployee($employeeUserB, $department, 'EMP-RPT-007');
    createReportEmployee($employeeUserC, $department, 'EMP-RPT-008');

    Attendance::query()->create([
        'employee_id' => $employeeA->id,
        'attendance_date' => '2030-03-10',
        'check_in_time' => '2030-03-10 08:00:00',
        'check_out_time' => '2030-03-10 16:00:00',
        'status' => 'present',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeB->id,
        'attendance_date' => '2030-03-10',
        'check_in_time' => '2030-03-10 09:30:00',
        'check_out_time' => '2030-03-10 16:30:00',
        'status' => 'late',
    ]);

    getJson('/api/reports/attendance?from_date=2030-03-01&to_date=2030-03-31', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('data.total_present', 1)
        ->assertJsonPath('data.total_late', 1)
        ->assertJsonPath('data.employees_with_attendance', 2)
        ->assertJsonPath('data.total_absent', 1);
});

it('salary report computes aggregation correctly', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Salary Aggregation Department', 'status' => 'active']);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = createReportEmployee($employeeUserA, $department, 'EMP-RPT-009');
    $employeeB = createReportEmployee($employeeUserB, $department, 'EMP-RPT-010');

    Salary::query()->create([
        'employee_id' => $employeeA->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 1000,
        'bonus' => 100,
        'deduction' => 50,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    Salary::query()->create([
        'employee_id' => $employeeB->id,
        'effective_from' => now()->toDateString(),
        'base_salary' => 2000,
        'bonus' => 0,
        'deduction' => 200,
        'currency' => 'USD',
        'created_by' => $admin->id,
    ]);

    getJson('/api/reports/salaries', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('data.total_salary_cost', 2850)
        ->assertJsonPath('data.average_salary', 1425)
        ->assertJsonPath('data.min_salary', 1050)
        ->assertJsonPath('data.max_salary', 1800);
});

it('leave statistics are computed correctly', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $departmentA = Department::query()->create(['name' => 'Report Leaves Department A', 'status' => 'active']);
    $departmentB = Department::query()->create(['name' => 'Report Leaves Department B', 'status' => 'active']);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = createReportEmployee($employeeUserA, $departmentA, 'EMP-RPT-011');
    $employeeB = createReportEmployee($employeeUserB, $departmentB, 'EMP-RPT-012');

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Approved leave',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'approved',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Pending leave',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'pending',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'leave_type' => 'other',
        'description' => 'Rejected leave',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'rejected',
    ]);

    getJson('/api/reports/leaves', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('data.total_leaves', 3)
        ->assertJsonPath('data.approved_leaves', 1)
        ->assertJsonPath('data.rejected_leaves', 1)
        ->assertJsonPath('data.pending_leaves', 1);
});

it('attendance report includes total overtime hours', function (): void {
    config()->set('attendance.work_end', '17:00:00');

    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $department = Department::query()->create(['name' => 'Report Overtime Department', 'status' => 'active']);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
    $employee = createReportEmployee($employeeUser, $department, 'EMP-RPT-013');

    Attendance::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2030-06-10',
        'check_in_time' => '2030-06-10 09:00:00',
        'check_out_time' => '2030-06-10 19:00:00',
        'status' => 'present',
    ]);

    getJson('/api/reports/attendance?from_date=2030-06-01&to_date=2030-06-30', [
        'Authorization' => 'Bearer '.reportingToken($admin),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_overtime_hours', 2);
});
