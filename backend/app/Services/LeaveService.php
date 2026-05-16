<?php

namespace App\Services;

use App\Exceptions\EmployeeProfileNotFoundException;
use App\Exceptions\LeaveAccessDeniedException;
use App\Exceptions\LeaveNotPendingException;
use App\Exceptions\LeaveOverlapException;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\LeaveRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class LeaveService
{
    public function __construct(private LeaveRepository $leaveRepository) {}

    public function listLeaves(User $actor, array $filters): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        $actor->loadMissing('role');

        return match ($actor->role?->name) {
            'admin' => $this->leaveRepository->paginateAll($filters, $perPage),
            'manager' => $this->leaveRepository->paginateByManager((string) $actor->id, $filters, $perPage),
            'employee' => $this->leaveRepository->paginateByEmployeeUser((string) $actor->id, $filters, $perPage),
            default => throw new LeaveAccessDeniedException,
        };
    }

    public function getLeaveById(User $actor, string $id): ?LeaveRequest
    {
        $actor->loadMissing('role');

        $leaveRequest = $this->leaveRepository->findById($id);
        if ($leaveRequest === null) {
            return null;
        }

        if ($actor->role?->name === 'admin') {
            return $leaveRequest;
        }

        if ($actor->role?->name === 'manager') {
            if (! $this->leaveRepository->isInManagerDepartment($id, (string) $actor->id)) {
                throw new LeaveAccessDeniedException;
            }

            return $leaveRequest;
        }

        if ($actor->role?->name === 'employee') {
            if (! $this->leaveRepository->isOwnedByEmployeeUser($id, (string) $actor->id)) {
                throw new LeaveAccessDeniedException;
            }

            return $leaveRequest;
        }

        throw new LeaveAccessDeniedException;
    }

    public function createLeave(User $actor, array $payload): LeaveRequest
    {
        $actor->loadMissing('role');

        if ($actor->role?->name !== 'employee') {
            throw new LeaveAccessDeniedException;
        }

        $employee = $this->leaveRepository->findEmployeeByUserId((string) $actor->id);
        if ($employee === null) {
            throw new EmployeeProfileNotFoundException;
        }

        if ($this->leaveRepository->hasOverlappingLeave(
            (string) $employee->id,
            (string) $payload['start_date'],
            (string) $payload['end_date']
        )) {
            throw new LeaveOverlapException;
        }

        $leave = $this->leaveRepository->create([
            'employee_id' => (string) $employee->id,
            'description' => (string) $payload['description'],
            'start_date' => (string) $payload['start_date'],
            'end_date' => (string) $payload['end_date'],
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'leave_type' => 'other',
            'total_days' => $this->calculateTotalDays((string) $payload['start_date'], (string) $payload['end_date']),
            'rejection_reason' => null,
        ]);

        Log::info('EMS leave apply', [
            'user_id' => $actor->id,
            'employee_id' => $leave->employee_id,
            'leave_id' => $leave->id,
            'status' => $leave->status,
        ]);

        return $leave;
    }

    public function updateLeave(User $actor, string $id, array $payload): ?LeaveRequest
    {
        $actor->loadMissing('role');

        if ($actor->role?->name !== 'employee') {
            throw new LeaveAccessDeniedException;
        }

        $leaveRequest = $this->leaveRepository->findById($id);
        if ($leaveRequest === null) {
            return null;
        }

        if (! $this->leaveRepository->isOwnedByEmployeeUser($id, (string) $actor->id)) {
            throw new LeaveAccessDeniedException;
        }

        if ($leaveRequest->status !== 'pending') {
            throw new LeaveNotPendingException;
        }

        if ($this->leaveRepository->hasOverlappingLeave(
            (string) $leaveRequest->employee_id,
            (string) $payload['start_date'],
            (string) $payload['end_date'],
            (string) $leaveRequest->id
        )) {
            throw new LeaveOverlapException;
        }

        return $this->leaveRepository->update($leaveRequest, [
            'description' => (string) $payload['description'],
            'start_date' => (string) $payload['start_date'],
            'end_date' => (string) $payload['end_date'],
            'total_days' => $this->calculateTotalDays((string) $payload['start_date'], (string) $payload['end_date']),
        ]);
    }

    public function approveLeave(User $actor, string $id): ?LeaveRequest
    {
        $actor->loadMissing('role');

        if ($actor->role?->name !== 'manager') {
            throw new LeaveAccessDeniedException;
        }

        $leaveRequest = $this->leaveRepository->findById($id);
        if ($leaveRequest === null) {
            return null;
        }

        if (! $this->leaveRepository->isInManagerDepartment($id, (string) $actor->id)) {
            throw new LeaveAccessDeniedException;
        }

        if ($leaveRequest->status !== 'pending') {
            throw new LeaveNotPendingException;
        }

        $approvedLeave = $this->leaveRepository->update($leaveRequest, [
            'status' => 'approved',
            'approved_by' => (string) $actor->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        Log::info('EMS leave approve', [
            'user_id' => $actor->id,
            'leave_id' => $approvedLeave->id,
            'employee_id' => $approvedLeave->employee_id,
            'status' => $approvedLeave->status,
        ]);

        return $approvedLeave;
    }

    public function rejectLeave(User $actor, string $id): ?LeaveRequest
    {
        $actor->loadMissing('role');

        if ($actor->role?->name !== 'manager') {
            throw new LeaveAccessDeniedException;
        }

        $leaveRequest = $this->leaveRepository->findById($id);
        if ($leaveRequest === null) {
            return null;
        }

        if (! $this->leaveRepository->isInManagerDepartment($id, (string) $actor->id)) {
            throw new LeaveAccessDeniedException;
        }

        if ($leaveRequest->status !== 'pending') {
            throw new LeaveNotPendingException;
        }

        $rejectedLeave = $this->leaveRepository->update($leaveRequest, [
            'status' => 'rejected',
            'approved_by' => (string) $actor->id,
            'approved_at' => now(),
        ]);

        Log::info('EMS leave reject', [
            'user_id' => $actor->id,
            'leave_id' => $rejectedLeave->id,
            'employee_id' => $rejectedLeave->employee_id,
            'status' => $rejectedLeave->status,
        ]);

        return $rejectedLeave;
    }

    private function calculateTotalDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return $start->diffInDays($end) + 1;
    }
}
