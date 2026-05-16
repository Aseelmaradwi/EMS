<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalaryRepository
{
    public function paginateSalaries(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Salary::query()
            ->with(['employee.user.role', 'employee.department'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['month']) && is_string($filters['month']) && $filters['month'] !== '') {
            [$year, $month] = explode('-', $filters['month']);

            $query
                ->whereYear('effective_from', (int) $year)
                ->whereMonth('effective_from', (int) $month);
        }

        return $query->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Salary
    {
        return Salary::query()
            ->with(['employee.user.role', 'employee.department'])
            ->find($id);
    }

    public function create(array $attributes): Salary
    {
        return Salary::query()
            ->create($attributes)
            ->load(['employee.user.role', 'employee.department']);
    }

    public function update(Salary $salary, array $attributes): Salary
    {
        $salary->fill($attributes);
        $salary->save();

        return $salary->load(['employee.user.role', 'employee.department']);
    }

    public function delete(Salary $salary): void
    {
        $salary->delete();
    }

    public function findEmployeeById(string $employeeId): ?Employee
    {
        return Employee::query()
            ->with(['user.role', 'department'])
            ->find($employeeId);
    }

    public function existsByEmployeeId(string $employeeId): bool
    {
        return Salary::query()
            ->where('employee_id', $employeeId)
            ->exists();
    }

    public function existsByEmployeeIdExceptSalary(string $employeeId, string $salaryId): bool
    {
        return Salary::query()
            ->where('employee_id', $employeeId)
            ->where('id', '!=', $salaryId)
            ->exists();
    }
}
