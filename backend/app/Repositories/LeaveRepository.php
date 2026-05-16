<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRepository
{
    public function paginateAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = LeaveRequest::query()
            ->with(['employee.user.role', 'employee.department', 'approver.role'])
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateByEmployeeUser(string $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = LeaveRequest::query()
            ->with(['employee.user.role', 'employee.department', 'approver.role'])
            ->whereHas('employee', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateByManager(string $managerUserId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = LeaveRequest::query()
            ->with(['employee.user.role', 'employee.department', 'approver.role'])
            ->whereHas('employee.department', function ($query) use ($managerUserId): void {
                $query->where('manager_id', $managerUserId);
            })
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?LeaveRequest
    {
        return LeaveRequest::query()
            ->with(['employee.user.role', 'employee.department', 'approver.role'])
            ->find($id);
    }

    public function create(array $attributes): LeaveRequest
    {
        return LeaveRequest::query()
            ->create($attributes)
            ->load(['employee.user.role', 'employee.department', 'approver.role']);
    }

    public function update(LeaveRequest $leaveRequest, array $attributes): LeaveRequest
    {
        $leaveRequest->fill($attributes);
        $leaveRequest->save();

        return $leaveRequest->load(['employee.user.role', 'employee.department', 'approver.role']);
    }

    public function findEmployeeByUserId(string $userId): ?Employee
    {
        return Employee::query()
            ->with(['user.role', 'department'])
            ->where('user_id', $userId)
            ->first();
    }

    public function isOwnedByEmployeeUser(string $leaveId, string $userId): bool
    {
        return LeaveRequest::query()
            ->whereKey($leaveId)
            ->whereHas('employee', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->exists();
    }

    public function isInManagerDepartment(string $leaveId, string $managerUserId): bool
    {
        return LeaveRequest::query()
            ->whereKey($leaveId)
            ->whereHas('employee.department', function ($query) use ($managerUserId): void {
                $query->where('manager_id', $managerUserId);
            })
            ->exists();
    }

    public function hasOverlappingLeave(
        string $employeeId,
        string $startDate,
        string $endDate,
        ?string $exceptLeaveId = null
    ): bool {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('status', '!=', 'rejected')
            ->when(
                $exceptLeaveId !== null,
                fn ($query) => $query->where('id', '!=', $exceptLeaveId)
            )
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();
    }

    private function applyFilters($query, array $filters): void
    {
        $query
            ->when(
                isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '',
                fn ($builder) => $builder->where('status', (string) $filters['status'])
            )
            ->when(
                isset($filters['employee_id']) && is_string($filters['employee_id']) && $filters['employee_id'] !== '',
                fn ($builder) => $builder->where('employee_id', (string) $filters['employee_id'])
            )
            ->when(
                isset($filters['from_date']) && is_string($filters['from_date']) && $filters['from_date'] !== '',
                fn ($builder) => $builder->whereDate('start_date', '>=', (string) $filters['from_date'])
            )
            ->when(
                isset($filters['to_date']) && is_string($filters['to_date']) && $filters['to_date'] !== '',
                fn ($builder) => $builder->whereDate('end_date', '<=', (string) $filters['to_date'])
            );
    }
}
