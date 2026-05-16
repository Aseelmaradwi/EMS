<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeRepository
{
    public function paginateEmployees(array $filters, int $perPage): LengthAwarePaginator
    {
        return Employee::query()
            ->with(['user.role', 'department'])
            ->when(
                isset($filters['name']) && is_string($filters['name']) && trim($filters['name']) !== '',
                function ($query) use ($filters) {
                    $name = trim((string) $filters['name']);

                    $query->whereHas('user', function ($userQuery) use ($name): void {
                        $userQuery->where('name', 'like', "%{$name}%");
                    });
                }
            )
            ->when(
                isset($filters['department_id']) && is_string($filters['department_id']) && $filters['department_id'] !== '',
                fn ($query) => $query->where('department_id', (string) $filters['department_id'])
            )
            ->when(
                isset($filters['role']) && is_string($filters['role']) && $filters['role'] !== '',
                function ($query) use ($filters): void {
                    $role = (string) $filters['role'];

                    $query->whereHas('user.role', function ($roleQuery) use ($role): void {
                        $roleQuery->where('name', $role);
                    });
                }
            )
            ->when(
                isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '',
                function ($query) use ($filters) {
                    $search = trim((string) $filters['search']);

                    $query->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Employee
    {
        return Employee::query()
            ->with(['user.role', 'department'])
            ->find($id);
    }

    public function create(array $attributes): Employee
    {
        return Employee::query()
            ->create($attributes)
            ->load(['user.role', 'department']);
    }

    public function update(Employee $employee, array $attributes): Employee
    {
        $employee->fill($attributes);
        $employee->save();

        return $employee->load(['user.role', 'department']);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function findUserByIdWithRole(string $id): ?User
    {
        return User::query()->with('role')->find($id);
    }

    public function findDepartmentById(string $id): ?Department
    {
        return Department::query()->find($id);
    }

    public function existsByUserId(string $userId): bool
    {
        return Employee::query()
            ->where('user_id', $userId)
            ->exists();
    }

    public function existsByUserIdExceptEmployee(string $userId, string $employeeId): bool
    {
        return Employee::query()
            ->where('user_id', $userId)
            ->where('id', '!=', $employeeId)
            ->exists();
    }

    public function existsByEmployeeCode(string $code): bool
    {
        return Employee::query()
            ->where('employee_code', $code)
            ->exists();
    }
}
