<?php

namespace App\Services;

use App\Exceptions\AttendanceAccessDeniedException;
use App\Exceptions\AttendanceAlreadyCheckedInException;
use App\Exceptions\AttendanceAlreadyCheckedOutException;
use App\Exceptions\AttendanceCheckInRequiredException;
use App\Exceptions\AttendanceOnApprovedLeaveException;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function __construct(private AttendanceRepository $attendanceRepository) {}

    public function checkIn(User $actor): Attendance
    {
        $employee = $this->resolveActorEmployeeOrFail($actor);

        $today = now()->toDateString();

        if ($this->attendanceRepository->hasApprovedLeaveForDate((string) $employee->id, $today)) {
            throw new AttendanceOnApprovedLeaveException;
        }

        $existingAttendance = $this->attendanceRepository->findByEmployeeAndDate((string) $employee->id, $today);
        if ($existingAttendance !== null) {
            throw new AttendanceAlreadyCheckedInException;
        }

        $attendance = $this->attendanceRepository->create([
            'employee_id' => (string) $employee->id,
            'attendance_date' => $today,
            'check_in_time' => now(),
            'check_out_time' => null,
            'status' => $this->resolveStatusFromCheckInTime(now()->format('H:i:s')),
            'notes' => null,
        ]);

        Log::info('EMS attendance check-in', [
            'user_id' => $actor->id,
            'employee_id' => $attendance->employee_id,
            'attendance_id' => $attendance->id,
            'attendance_date' => (string) $attendance->attendance_date,
        ]);

        return $attendance;
    }

    public function checkOut(User $actor): Attendance
    {
        $employee = $this->resolveActorEmployeeOrFail($actor);

        $todayAttendance = $this->attendanceRepository->findByEmployeeAndDate((string) $employee->id, now()->toDateString());
        if ($todayAttendance === null || $todayAttendance->check_in_time === null) {
            throw new AttendanceCheckInRequiredException;
        }

        if ($todayAttendance->check_out_time !== null) {
            throw new AttendanceAlreadyCheckedOutException;
        }

        $attendance = $this->attendanceRepository->update($todayAttendance, [
            'check_out_time' => now(),
        ]);

        Log::info('EMS attendance check-out', [
            'user_id' => $actor->id,
            'employee_id' => $attendance->employee_id,
            'attendance_id' => $attendance->id,
            'attendance_date' => (string) $attendance->attendance_date,
        ]);

        if ($attendance->overtime_hours > 0) {
            Log::info('EMS attendance overtime detected', [
                'user_id' => $actor->id,
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'overtime_hours' => $attendance->overtime_hours,
            ]);
        }

        return $attendance;
    }

    public function markAbsentForDate(string $date): int
    {
        $createdAbsentCount = 0;

        foreach ($this->attendanceRepository->allEmployeeIds() as $employeeId) {
            if ($this->attendanceRepository->hasApprovedLeaveForDate($employeeId, $date)) {
                continue;
            }

            $attendance = $this->attendanceRepository->firstOrCreateAbsentByEmployeeAndDate($employeeId, $date);

            if (! $attendance->wasRecentlyCreated) {
                continue;
            }

            $createdAbsentCount++;

            Log::info('EMS auto absent record created', [
                'employee_id' => $employeeId,
                'attendance_id' => $attendance->id,
                'attendance_date' => $date,
            ]);
        }

        return $createdAbsentCount;
    }

    public function listAttendance(User $actor, array $filters): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        $roleName = $this->resolveRoleName($actor);

        return match ($roleName) {
            'admin' => $this->attendanceRepository->paginateAll($filters, $perPage),
            'manager' => $this->attendanceRepository->paginateByManager((string) $actor->id, $filters, $perPage),
            'employee' => $this->attendanceRepository->paginateByEmployeeUser((string) $actor->id, $filters, $perPage),
            default => throw new AttendanceAccessDeniedException,
        };
    }

    public function getAttendanceById(User $actor, string $id): ?Attendance
    {
        $attendance = $this->attendanceRepository->findById($id);
        if ($attendance === null) {
            return null;
        }

        $roleName = $this->resolveRoleName($actor);

        if ($roleName === 'admin') {
            return $attendance;
        }

        if ($roleName === 'manager') {
            if (! $this->attendanceRepository->isInManagerDepartment($id, (string) $actor->id)) {
                throw new AttendanceAccessDeniedException;
            }

            return $attendance;
        }

        if ($roleName === 'employee') {
            if (! $this->attendanceRepository->isOwnedByEmployeeUser($id, (string) $actor->id)) {
                throw new AttendanceAccessDeniedException;
            }

            return $attendance;
        }

        throw new AttendanceAccessDeniedException;
    }

    private function resolveRoleName(User $actor): string
    {
        $actor->loadMissing('role');

        return (string) $actor->role?->name;
    }

    private function resolveStatusFromCheckInTime(string $checkInTime): string
    {
        $lateAfter = (string) config('attendance.late_after', '09:15:00');

        return $checkInTime > $lateAfter ? 'late' : 'present';
    }

    private function resolveActorEmployeeOrFail(User $actor): Employee
    {
        $employee = $this->attendanceRepository->findEmployeeByUserId((string) $actor->id);

        if ($employee === null) {
            throw new AttendanceAccessDeniedException;
        }

        return $employee;
    }
}
