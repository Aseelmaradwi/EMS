<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

function attendanceToken(User $user): string
{
    return (string) JWTAuth::fromUser($user);
}

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'System administrator role']);
    Role::query()->firstOrCreate(['name' => 'manager'], ['description' => 'Department manager role']);
    Role::query()->firstOrCreate(['name' => 'employee'], ['description' => 'Standard employee role']);
});

it('employee can check-in', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Check-In Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-001',
        'first_name' => 'Attendance',
        'last_name' => 'Checkin',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Check-in recorded successfully.')
        ->assertJsonPath('data.attendance.employee_id', $employee->id);
});

it('manager with employee profile can check-in', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Manager Check-In Department',
        'status' => 'active',
    ]);

    $managerEmployee = Employee::query()->create([
        'user_id' => $managerUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-MNG-001',
        'first_name' => 'Manager',
        'last_name' => 'Checkin',
        'email' => $managerUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($managerUser),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Check-in recorded successfully.')
        ->assertJsonPath('data.attendance.employee_id', $managerEmployee->id);
});

it('manager without employee profile is forbidden from check-in', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('message', 'Forbidden.');
});

it('admin without employee profile is forbidden from check-in', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($adminUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN')
        ->assertJsonPath('message', 'Forbidden.');
});

it('employee cannot check-in twice', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Duplicate Check-In Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-002',
        'first_name' => 'Duplicate',
        'last_name' => 'Checkin',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertCreated();

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertStatus(422)
        ->assertJsonPath('code', 'ATTENDANCE_ALREADY_CHECKED_IN');
});

it('employee can check-out', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Check-Out Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-003',
        'first_name' => 'Attendance',
        'last_name' => 'Checkout',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertCreated();

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Check-out recorded successfully.')
        ->assertJsonPath('data.attendance.check_out_at', fn (mixed $value): bool => is_string($value) && $value !== '');
});

it('cannot check-out without check-in', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Checkout Missing Checkin Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-004',
        'first_name' => 'Missing',
        'last_name' => 'Checkin',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertStatus(422)
        ->assertJsonPath('code', 'ATTENDANCE_CHECK_IN_REQUIRED');
});

it('cannot check-out twice', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Checkout Twice Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-005',
        'first_name' => 'Double',
        'last_name' => 'Checkout',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertCreated();

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertOk();

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertStatus(422)
        ->assertJsonPath('code', 'ATTENDANCE_ALREADY_CHECKED_OUT');
});

it('leave blocks check-in', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Leave Block Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-006',
        'first_name' => 'Leave',
        'last_name' => 'Blocked',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Approved leave',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'approved',
    ]);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])->assertStatus(422)
        ->assertJsonPath('code', 'ATTENDANCE_ON_APPROVED_LEAVE')
        ->assertJsonPath('message', 'Cannot check-in while on approved leave.');
});

it('manager sees department attendance', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);
    $otherManagerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $departmentA = Department::query()->create([
        'name' => 'Attendance Manager Department A',
        'status' => 'active',
        'manager_id' => $managerUser->id,
    ]);

    $departmentB = Department::query()->create([
        'name' => 'Attendance Manager Department B',
        'status' => 'active',
        'manager_id' => $otherManagerUser->id,
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $departmentA->id,
        'employee_code' => 'EMP-ATD-007',
        'first_name' => 'Department',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $departmentB->id,
        'employee_code' => 'EMP-ATD-008',
        'first_name' => 'Department',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeA->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->subHours(3),
        'check_out_time' => now()->subHours(1),
        'status' => 'present',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeB->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->subHours(3),
        'check_out_time' => now()->subHours(1),
        'status' => 'present',
    ]);

    getJson('/api/attendance', [
        'Authorization' => 'Bearer '.attendanceToken($managerUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.attendance.0.employee_id', $employeeA->id);
});

it('manager cannot see others attendance by id', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);
    $otherManagerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Restricted Department',
        'status' => 'active',
        'manager_id' => $otherManagerUser->id,
    ]);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-009',
        'first_name' => 'Restricted',
        'last_name' => 'Attendance',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $attendance = Attendance::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->subHours(2),
        'check_out_time' => now()->subHour(),
        'status' => 'present',
    ]);

    getJson('/api/attendance/'.$attendance->id, [
        'Authorization' => 'Bearer '.attendanceToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('admin sees all attendance', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Admin Department',
        'status' => 'active',
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-010',
        'first_name' => 'Admin',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-011',
        'first_name' => 'Admin',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeA->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->subHours(2),
        'check_out_time' => now()->subHour(),
        'status' => 'present',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeB->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->subHours(2),
        'check_out_time' => now()->subHour(),
        'status' => 'present',
    ]);

    getJson('/api/attendance', [
        'Authorization' => 'Bearer '.attendanceToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 2);
});

it('total_hours computed correctly via accessor', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);
    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Total Hours Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-012',
        'first_name' => 'Total',
        'last_name' => 'Hours',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $attendance = Attendance::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => now()->toDateString(),
        'check_in_time' => now()->startOfDay()->addHours(8),
        'check_out_time' => now()->startOfDay()->addHours(16)->addMinutes(30),
        'status' => 'present',
    ]);

    getJson('/api/attendance/'.$attendance->id, [
        'Authorization' => 'Bearer '.attendanceToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.attendance.total_hours', 8.5);
});

it('admin can filter attendance by from_date to_date and employee_id', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    $department = Department::query()->create([
        'name' => 'Attendance Filter Department',
        'status' => 'active',
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-FLTR-001',
        'first_name' => 'Range',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-ATD-FLTR-002',
        'first_name' => 'Range',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeA->id,
        'attendance_date' => '2031-01-10',
        'check_in_time' => '2031-01-10 08:00:00',
        'check_out_time' => '2031-01-10 16:00:00',
        'status' => 'present',
    ]);

    Attendance::query()->create([
        'employee_id' => $employeeB->id,
        'attendance_date' => '2031-02-10',
        'check_in_time' => '2031-02-10 08:00:00',
        'check_out_time' => '2031-02-10 16:00:00',
        'status' => 'present',
    ]);

    getJson('/api/attendance?from_date=2031-01-01&to_date=2031-01-31&employee_id='.$employeeA->id, [
        'Authorization' => 'Bearer '.attendanceToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.attendance.0.employee_id', $employeeA->id);
});

it('attendance index validates to_date after_or_equal from_date', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    getJson('/api/attendance?from_date=2031-03-10&to_date=2031-03-01', [
        'Authorization' => 'Bearer '.attendanceToken($adminUser),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['to_date'],
        ]);
});
