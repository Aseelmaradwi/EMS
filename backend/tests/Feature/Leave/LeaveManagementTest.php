<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function leaveToken(User $user): string
{
    return (string) JWTAuth::fromUser($user);
}

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'System administrator role']);
    Role::query()->firstOrCreate(['name' => 'manager'], ['description' => 'Department manager role']);
    Role::query()->firstOrCreate(['name' => 'employee'], ['description' => 'Standard employee role']);
});

it('employee can apply leave', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.leave.apply@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Engineering Leaves',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-001',
        'first_name' => 'Leave',
        'last_name' => 'Applicant',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/leaves', [
        'description' => 'Annual leave for family event',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
    ], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Leave request created successfully.')
        ->assertJsonPath('data.leave.employee_id', $employee->id)
        ->assertJsonPath('data.leave.status', 'pending');
});

it('employee sees only own leaves', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUserA = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.leave.owner.a@example.com',
    ]);

    $employeeUserB = User::factory()->create([
        'role_id' => $employeeRole->id,
        'email' => 'employee.leave.owner.b@example.com',
    ]);

    $department = Department::query()->create([
        'name' => 'Employee Scope Department',
        'status' => 'active',
    ]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-002',
        'first_name' => 'Owner',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-003',
        'first_name' => 'Owner',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Leave A',
        'start_date' => now()->addDays(2)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'leave_type' => 'other',
        'description' => 'Leave B',
        'start_date' => now()->addDays(2)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    getJson('/api/leaves', [
        'Authorization' => 'Bearer '.leaveToken($employeeUserA),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.leaves.0.employee_id', $employeeA->id);
});

it('manager sees department leaves only', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.leave.scope@example.com',
    ]);

    $otherManagerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'email' => 'manager.leave.scope.other@example.com',
    ]);

    $departmentA = Department::query()->create([
        'name' => 'Managed Department A',
        'status' => 'active',
        'manager_id' => $managerUser->id,
    ]);

    $departmentB = Department::query()->create([
        'name' => 'Managed Department B',
        'status' => 'active',
        'manager_id' => $otherManagerUser->id,
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $departmentA->id,
        'employee_code' => 'EMP-LEAVE-004',
        'first_name' => 'Dept',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $departmentB->id,
        'employee_code' => 'EMP-LEAVE-005',
        'first_name' => 'Dept',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Managed leave',
        'start_date' => now()->addDays(6)->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'leave_type' => 'other',
        'description' => 'Other department leave',
        'start_date' => now()->addDays(6)->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    getJson('/api/leaves', [
        'Authorization' => 'Bearer '.leaveToken($managerUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.leaves.0.employee_id', $employeeA->id);
});

it('manager cannot access other department leaves', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);
    $otherManagerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $department = Department::query()->create([
        'name' => 'Other Managed Department',
        'status' => 'active',
        'manager_id' => $otherManagerUser->id,
    ]);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-006',
        'first_name' => 'Other',
        'last_name' => 'Dept',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $leave = LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Restricted leave',
        'start_date' => now()->addDays(8)->toDateString(),
        'end_date' => now()->addDays(9)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    getJson('/api/leaves/'.$leave->id, [
        'Authorization' => 'Bearer '.leaveToken($managerUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('manager can approve leave in managed department', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $department = Department::query()->create([
        'name' => 'Approval Department',
        'status' => 'active',
        'manager_id' => $managerUser->id,
    ]);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-007',
        'first_name' => 'Approve',
        'last_name' => 'Target',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $leave = LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Approval leave',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(11)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    patchJson('/api/leaves/'.$leave->id.'/approve', [], [
        'Authorization' => 'Bearer '.leaveToken($managerUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.leave.status', 'approved')
        ->assertJsonPath('data.leave.approved_by', $managerUser->id);
});

it('manager can reject leave in managed department', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);

    $department = Department::query()->create([
        'name' => 'Rejection Department',
        'status' => 'active',
        'manager_id' => $managerUser->id,
    ]);

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-008',
        'first_name' => 'Reject',
        'last_name' => 'Target',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $leave = LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Rejection leave',
        'start_date' => now()->addDays(12)->toDateString(),
        'end_date' => now()->addDays(13)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    patchJson('/api/leaves/'.$leave->id.'/reject', [], [
        'Authorization' => 'Bearer '.leaveToken($managerUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.leave.status', 'rejected')
        ->assertJsonPath('data.leave.approved_by', $managerUser->id);
});

it('employee cannot approve leave', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Employee Approve Restriction',
        'status' => 'active',
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-009',
        'first_name' => 'No',
        'last_name' => 'Approve',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $leave = LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'Employee approve denied',
        'start_date' => now()->addDays(14)->toDateString(),
        'end_date' => now()->addDays(15)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    patchJson('/api/leaves/'.$leave->id.'/approve', [], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('admin can view all leaves', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    $department = Department::query()->create([
        'name' => 'Admin Leave Visibility',
        'status' => 'active',
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-010',
        'first_name' => 'Admin',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-011',
        'first_name' => 'Admin',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Admin view one',
        'start_date' => now()->addDays(2)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'leave_type' => 'other',
        'description' => 'Admin view two',
        'start_date' => now()->addDays(4)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    getJson('/api/leaves?per_page=5', [
        'Authorization' => 'Bearer '.leaveToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.per_page', 5);
});

it('overlapping leaves are blocked', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Overlap Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-012',
        'first_name' => 'Overlap',
        'last_name' => 'Employee',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/leaves', [
        'description' => 'Initial leave',
        'start_date' => now()->addDays(20)->toDateString(),
        'end_date' => now()->addDays(22)->toDateString(),
    ], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertCreated();

    postJson('/api/leaves', [
        'description' => 'Overlapping leave',
        'start_date' => now()->addDays(21)->toDateString(),
        'end_date' => now()->addDays(24)->toDateString(),
    ], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertStatus(422)
        ->assertJsonPath('code', 'LEAVE_OVERLAP');
});

it('admin can filter leaves by status employee_id and date range', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    $department = Department::query()->create([
        'name' => 'Leave Filter Department',
        'status' => 'active',
    ]);

    $employeeUserA = User::factory()->create(['role_id' => $employeeRole->id]);
    $employeeUserB = User::factory()->create(['role_id' => $employeeRole->id]);

    $employeeA = Employee::query()->create([
        'user_id' => $employeeUserA->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-FLTR-001',
        'first_name' => 'Filter',
        'last_name' => 'A',
        'email' => $employeeUserA->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $employeeB = Employee::query()->create([
        'user_id' => $employeeUserB->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-FLTR-002',
        'first_name' => 'Filter',
        'last_name' => 'B',
        'email' => $employeeUserB->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'leave_type' => 'other',
        'description' => 'Approved in range',
        'start_date' => '2032-01-10',
        'end_date' => '2032-01-12',
        'total_days' => 3,
        'status' => 'approved',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'leave_type' => 'other',
        'description' => 'Pending out of range',
        'start_date' => '2032-02-10',
        'end_date' => '2032-02-12',
        'total_days' => 3,
        'status' => 'pending',
    ]);

    getJson('/api/leaves?status=approved&employee_id='.$employeeA->id.'&from_date=2032-01-01&to_date=2032-01-31', [
        'Authorization' => 'Bearer '.leaveToken($adminUser),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.leaves.0.employee_id', $employeeA->id)
        ->assertJsonPath('data.leaves.0.status', 'approved');
});

it('leave index validates to_date after_or_equal from_date', function (): void {
    $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
    $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

    getJson('/api/leaves?from_date=2032-04-10&to_date=2032-04-01', [
        'Authorization' => 'Bearer '.leaveToken($adminUser),
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed.')
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure([
            'errors' => ['to_date'],
        ]);
});

it('returns validation errors for invalid dates', function (): void {
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Date Validation Department',
        'status' => 'active',
    ]);

    Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-013',
        'first_name' => 'Date',
        'last_name' => 'Validation',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    postJson('/api/leaves', [
        'description' => 'Invalid date sequence',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(9)->toDateString(),
    ], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('cannot update approved leave', function (): void {
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $managerUser = User::factory()->create(['role_id' => $managerRole->id]);
    $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);

    $department = Department::query()->create([
        'name' => 'Update Restriction Department',
        'status' => 'active',
        'manager_id' => $managerUser->id,
    ]);

    $employee = Employee::query()->create([
        'user_id' => $employeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-LEAVE-014',
        'first_name' => 'Update',
        'last_name' => 'Blocked',
        'email' => $employeeUser->email,
        'hire_date' => now()->toDateString(),
        'employment_status' => 'active',
    ]);

    $leave = LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'leave_type' => 'other',
        'description' => 'To approve then update',
        'start_date' => now()->addDays(16)->toDateString(),
        'end_date' => now()->addDays(17)->toDateString(),
        'total_days' => 2,
        'status' => 'pending',
    ]);

    patchJson('/api/leaves/'.$leave->id.'/approve', [], [
        'Authorization' => 'Bearer '.leaveToken($managerUser),
    ])->assertOk();

    putJson('/api/leaves/'.$leave->id, [
        'description' => 'Attempt update approved leave',
        'start_date' => now()->addDays(16)->toDateString(),
        'end_date' => now()->addDays(18)->toDateString(),
    ], [
        'Authorization' => 'Bearer '.leaveToken($employeeUser),
    ])->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});
