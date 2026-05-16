<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;

class AttendanceRepository
{
    public function paginateAll(array $filters, int $perPage): LengthAwarePaginator
    {
        return Attendance::query()
            ->with(['employee.user.role', 'employee.department'])
            ->when(
                isset($filters['from_date']) && is_string($filters['from_date']) && $filters['from_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '>=', (string) $filters['from_date'])
            )
            ->when(
                isset($filters['to_date']) && is_string($filters['to_date']) && $filters['to_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '<=', (string) $filters['to_date'])
            )
            ->when(
                isset($filters['date']) && is_string($filters['date']) && $filters['date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', (string) $filters['date'])
            )
            ->when(
                isset($filters['employee_id']) && is_string($filters['employee_id']) && $filters['employee_id'] !== '',
                fn ($query) => $query->where('employee_id', (string) $filters['employee_id'])
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateByManager(string $managerUserId, array $filters, int $perPage): LengthAwarePaginator
    {
        return Attendance::query()
            ->with(['employee.user.role', 'employee.department'])
            ->whereHas('employee.department', function ($query) use ($managerUserId): void {
                $query->where('manager_id', $managerUserId);
            })
            ->when(
                isset($filters['from_date']) && is_string($filters['from_date']) && $filters['from_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '>=', (string) $filters['from_date'])
            )
            ->when(
                isset($filters['to_date']) && is_string($filters['to_date']) && $filters['to_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '<=', (string) $filters['to_date'])
            )
            ->when(
                isset($filters['date']) && is_string($filters['date']) && $filters['date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', (string) $filters['date'])
            )
            ->when(
                isset($filters['employee_id']) && is_string($filters['employee_id']) && $filters['employee_id'] !== '',
                fn ($query) => $query->where('employee_id', (string) $filters['employee_id'])
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateByEmployeeUser(string $employeeUserId, array $filters, int $perPage): LengthAwarePaginator
    {
        return Attendance::query()
            ->with(['employee.user.role', 'employee.department'])
            ->whereHas('employee', function ($query) use ($employeeUserId): void {
                $query->where('user_id', $employeeUserId);
            })
            ->when(
                isset($filters['from_date']) && is_string($filters['from_date']) && $filters['from_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '>=', (string) $filters['from_date'])
            )
            ->when(
                isset($filters['to_date']) && is_string($filters['to_date']) && $filters['to_date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', '<=', (string) $filters['to_date'])
            )
            ->when(
                isset($filters['date']) && is_string($filters['date']) && $filters['date'] !== '',
                fn ($query) => $query->whereDate('attendance_date', (string) $filters['date'])
            )
            ->when(
                isset($filters['employee_id']) && is_string($filters['employee_id']) && $filters['employee_id'] !== '',
                fn ($query) => $query->where('employee_id', (string) $filters['employee_id'])
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Attendance
    {
        return Attendance::query()
            ->with(['employee.user.role', 'employee.department'])
            ->find($id);
    }

    public function create(array $attributes): Attendance
    {
        return Attendance::query()
            ->create($attributes)
            ->load(['employee.user.role', 'employee.department']);
    }

    public function update(Attendance $attendance, array $attributes): Attendance
    {
        $attendance->fill($attributes);
        $attendance->save();

        return $attendance->load(['employee.user.role', 'employee.department']);
    }

    public function findEmployeeByUserId(string $userId): ?Employee
    {
        return Employee::query()
            ->with(['user.role', 'department'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findByEmployeeAndDate(string $employeeId, string $date): ?Attendance
    {
        return Attendance::query()
            ->with(['employee.user.role', 'employee.department'])
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $date)
            ->first();
    }

    public function isOwnedByEmployeeUser(string $attendanceId, string $employeeUserId): bool
    {
        return Attendance::query()
            ->whereKey($attendanceId)
            ->whereHas('employee', function ($query) use ($employeeUserId): void {
                $query->where('user_id', $employeeUserId);
            })
            ->exists();
    }

    public function isInManagerDepartment(string $attendanceId, string $managerUserId): bool
    {
        return Attendance::query()
            ->whereKey($attendanceId)
            ->whereHas('employee.department', function ($query) use ($managerUserId): void {
                $query->where('manager_id', $managerUserId);
            })
            ->exists();
    }

    public function hasApprovedLeaveForDate(string $employeeId, string $date): bool
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function allEmployeeIds(): array
    {
        return Employee::query()
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }

    public function firstOrCreateAbsentByEmployeeAndDate(string $employeeId, string $date): Attendance
    {
        $existing = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $date)
            ->first();

        if ($existing !== null) {
            return $existing->load(['employee.user.role', 'employee.department']);
        }

        try {
            $attendance = Attendance::query()->create([
                'employee_id' => $employeeId,
                'attendance_date' => $date,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => 'absent',
                'notes' => null,
            ]);
        } catch (QueryException) {
            $attendance = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $date)
                ->firstOrFail();
        }

        return $attendance->load(['employee.user.role', 'employee.department']);
    }
}
