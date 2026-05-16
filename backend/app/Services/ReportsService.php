<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Salary;
use App\Models\User;
use App\Services\Concerns\EnsuresAdminAccess;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportsService
{
    use EnsuresAdminAccess;

    public function employeesReport(User $actor): array
    {
        $this->ensureAdmin($actor);

        $totalEmployees = Employee::query()->count();

        $employeesPerDepartment = Department::query()
            ->leftJoin('employees', function ($join): void {
                $join->on('departments.id', '=', 'employees.department_id')
                    ->whereNull('employees.deleted_at');
            })
            ->whereNull('departments.deleted_at')
            ->select([
                'departments.id as department_id',
                'departments.name as department_name',
                DB::raw('COUNT(employees.id) as employees_count'),
            ])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        $employeesPerRole = DB::table('employees')
            ->join('users', function ($join): void {
                $join->on('employees.user_id', '=', 'users.id')
                    ->whereNull('users.deleted_at');
            })
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('employees.deleted_at')
            ->select([
                'roles.id as role_id',
                'roles.name as role_name',
                DB::raw('COUNT(employees.id) as employees_count'),
            ])
            ->groupBy('roles.id', 'roles.name')
            ->orderBy('roles.name')
            ->get();

        return [
            'total_employees' => $totalEmployees,
            'employees_per_department' => $employeesPerDepartment,
            'employees_per_role' => $employeesPerRole,
        ];
    }

    public function departmentsReport(User $actor): array
    {
        $this->ensureAdmin($actor);

        $totalDepartments = Department::query()->count();

        $employeesCountPerDepartment = Department::query()
            ->leftJoin('employees', function ($join): void {
                $join->on('departments.id', '=', 'employees.department_id')
                    ->whereNull('employees.deleted_at');
            })
            ->whereNull('departments.deleted_at')
            ->select([
                'departments.id as department_id',
                'departments.name as department_name',
                DB::raw('COUNT(employees.id) as employees_count'),
            ])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        $managerPerDepartment = Department::query()
            ->leftJoin('users as managers', function ($join): void {
                $join->on('departments.manager_id', '=', 'managers.id')
                    ->whereNull('managers.deleted_at');
            })
            ->whereNull('departments.deleted_at')
            ->select([
                'departments.id as department_id',
                'departments.name as department_name',
                'managers.id as manager_id',
                'managers.name as manager_name',
                'managers.email as manager_email',
            ])
            ->orderBy('departments.name')
            ->get();

        return [
            'total_departments' => $totalDepartments,
            'employees_count_per_department' => $employeesCountPerDepartment,
            'manager_per_department' => $managerPerDepartment,
        ];
    }

    public function attendanceReport(User $actor, array $filters): array
    {
        $this->ensureAdmin($actor);

        $fromDate = isset($filters['from_date']) && is_string($filters['from_date']) && $filters['from_date'] !== ''
            ? $filters['from_date']
            : now()->toDateString();

        $toDate = isset($filters['to_date']) && is_string($filters['to_date']) && $filters['to_date'] !== ''
            ? $filters['to_date']
            : $fromDate;

        if ($toDate < $fromDate) {
            throw new RuntimeException('The to_date must be a date after or equal to from_date.');
        }

        $attendanceBaseQuery = DB::table('attendance')
            ->whereDate('attendance_date', '>=', $fromDate)
            ->whereDate('attendance_date', '<=', $toDate);

        $totalPresent = (clone $attendanceBaseQuery)
            ->where('status', 'present')
            ->count();

        $totalLate = (clone $attendanceBaseQuery)
            ->where('status', 'late')
            ->count();

        $employeesWithAttendance = (clone $attendanceBaseQuery)
            ->distinct('employee_id')
            ->count('employee_id');

        $workEnd = (string) config('attendance.work_end', '17:00:00');
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $totalOvertimeMinutes = (clone $attendanceBaseQuery)
                ->selectRaw(
                    "COALESCE(SUM(CASE WHEN check_out_time IS NOT NULL AND check_out_time > datetime(date(attendance_date) || ' ' || ?) THEN ((julianday(check_out_time) - julianday(datetime(date(attendance_date) || ' ' || ?))) * 24 * 60) ELSE 0 END), 0) as overtime_minutes",
                    [$workEnd, $workEnd]
                )
                ->value('overtime_minutes');
        } else {
            $totalOvertimeMinutes = (clone $attendanceBaseQuery)
                ->selectRaw(
                    "COALESCE(SUM(CASE WHEN check_out_time IS NOT NULL AND check_out_time > CONCAT(DATE(attendance_date), ' ', ?) THEN TIMESTAMPDIFF(MINUTE, CONCAT(DATE(attendance_date), ' ', ?), check_out_time) ELSE 0 END), 0) as overtime_minutes",
                    [$workEnd, $workEnd]
                )
                ->value('overtime_minutes');
        }

        $totalOvertimeHours = round(((float) $totalOvertimeMinutes) / 60, 2);

        $totalEmployees = Employee::query()->count();
        $totalAbsent = max($totalEmployees - $employeesWithAttendance, 0);
        $attendancePercentage = $totalEmployees === 0
            ? 0.0
            : round(($totalPresent / $totalEmployees) * 100, 2);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'total_present' => $totalPresent,
            'total_late' => $totalLate,
            'total_absent' => $totalAbsent,
            'total_overtime_hours' => $totalOvertimeHours,
            'employees_with_attendance' => $employeesWithAttendance,
            'attendance_percentage' => $attendancePercentage,
        ];
    }

    public function salariesReport(User $actor): array
    {
        $this->ensureAdmin($actor);

        $summary = Salary::query()
            ->selectRaw('COALESCE(SUM(base_salary + bonus - deduction), 0) as total_salary_cost')
            ->selectRaw('COALESCE(AVG(base_salary + bonus - deduction), 0) as average_salary')
            ->selectRaw('COALESCE(MIN(base_salary + bonus - deduction), 0) as min_salary')
            ->selectRaw('COALESCE(MAX(base_salary + bonus - deduction), 0) as max_salary')
            ->first();

        $salaryDistributionPerDepartment = Department::query()
            ->leftJoin('employees', function ($join): void {
                $join->on('departments.id', '=', 'employees.department_id')
                    ->whereNull('employees.deleted_at');
            })
            ->leftJoin('salaries', 'employees.id', '=', 'salaries.employee_id')
            ->whereNull('departments.deleted_at')
            ->select([
                'departments.id as department_id',
                'departments.name as department_name',
                DB::raw('COALESCE(SUM(salaries.base_salary + salaries.bonus - salaries.deduction), 0) as total_salary_cost'),
                DB::raw('COALESCE(AVG(salaries.base_salary + salaries.bonus - salaries.deduction), 0) as average_salary'),
                DB::raw('COUNT(salaries.id) as salary_records_count'),
            ])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        return [
            'total_salary_cost' => round((float) ($summary?->total_salary_cost ?? 0), 2),
            'average_salary' => round((float) ($summary?->average_salary ?? 0), 2),
            'min_salary' => round((float) ($summary?->min_salary ?? 0), 2),
            'max_salary' => round((float) ($summary?->max_salary ?? 0), 2),
            'salary_distribution_per_department' => $salaryDistributionPerDepartment,
        ];
    }

    public function leavesReport(User $actor): array
    {
        $this->ensureAdmin($actor);

        $summary = LeaveRequest::query()
            ->selectRaw('COUNT(*) as total_leaves')
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_leaves")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_leaves")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_leaves")
            ->first();

        $leavesPerDepartment = Department::query()
            ->leftJoin('employees', function ($join): void {
                $join->on('departments.id', '=', 'employees.department_id')
                    ->whereNull('employees.deleted_at');
            })
            ->leftJoin('leave_requests', 'employees.id', '=', 'leave_requests.employee_id')
            ->whereNull('departments.deleted_at')
            ->select([
                'departments.id as department_id',
                'departments.name as department_name',
                DB::raw('COUNT(leave_requests.id) as leaves_count'),
            ])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        return [
            'total_leaves' => (int) ($summary?->total_leaves ?? 0),
            'approved_leaves' => (int) ($summary?->approved_leaves ?? 0),
            'rejected_leaves' => (int) ($summary?->rejected_leaves ?? 0),
            'pending_leaves' => (int) ($summary?->pending_leaves ?? 0),
            'leaves_per_department' => $leavesPerDepartment,
        ];
    }
}
