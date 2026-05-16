<?php

use App\Jobs\MarkEmployeesAbsentJob;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

function autoAbsentToken(User $user): string
{
    return (string) JWTAuth::fromUser($user);
}

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'System administrator role']);
    Role::query()->firstOrCreate(['name' => 'manager'], ['description' => 'Department manager role']);
    Role::query()->firstOrCreate(['name' => 'employee'], ['description' => 'Standard employee role']);
});

it('employee without check-in becomes absent', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Auto Absent Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-AUTO-001',
        'first_name' => 'Auto',
        'last_name' => 'Absent',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $job = new MarkEmployeesAbsentJob;
    $job->handle(app(AttendanceService::class));

    $absentRecord = Attendance::query()
        ->where('employee_id', $employee->id)
        ->whereDate('attendance_date', now()->toDateString())
        ->first();

    expect($absentRecord)->not->toBeNull();
    expect($absentRecord?->status)->toBe('absent');
    expect($absentRecord?->check_in_time)->toBeNull();
    expect($absentRecord?->check_out_time)->toBeNull();
});

it('approved leave prevents auto absent creation', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Auto Absent Leave Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-AUTO-002',
        'first_name' => 'Leave',
        'last_name' => 'Protected',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Approved leave window',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'total_days' => 1,
        'status' => 'approved',
    ]);

    $job = new MarkEmployeesAbsentJob;
    $job->handle(app(AttendanceService::class));

    expect(
        Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->count()
    )->toBe(0);
});

it('auto absent job does not create duplicate attendance records', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Auto Absent Dedup Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-AUTO-003',
        'first_name' => 'No',
        'last_name' => 'Duplicate',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $job = new MarkEmployeesAbsentJob;
    $job->handle(app(AttendanceService::class));
    $job->handle(app(AttendanceService::class));

    expect(
        Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->count()
    )->toBe(1);
});

it('attendance resource returns overtime hours correctly', function (): void {
    config()->set('attendance.work_end', '17:00:00');

    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);
    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Overtime Department',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-AUTO-004',
        'first_name' => 'Over',
        'last_name' => 'Time',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $attendance = Attendance::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2033-01-01',
        'check_in_time' => '2033-01-01 09:00:00',
        'check_out_time' => '2033-01-01 19:30:00',
        'status' => 'present',
    ]);

    getJson('/api/attendance/'.$attendance->id, [
        'Authorization' => 'Bearer '.autoAbsentToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.attendance.overtime_hours', 2.5);
});
