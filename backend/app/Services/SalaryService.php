<?php

namespace App\Services;

use App\Exceptions\SalaryAlreadyExistsException;
use App\Models\Salary;
use App\Models\User;
use App\Repositories\SalaryRepository;
use App\Services\Concerns\EnsuresAdminAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SalaryService
{
    use EnsuresAdminAccess;

    public function __construct(private SalaryRepository $salaryRepository) {}

    public function listSalaries(User $actor, array $filters): LengthAwarePaginator
    {
        $this->ensureAdmin($actor);

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $this->salaryRepository->paginateSalaries($filters, $perPage);
    }

    public function getSalaryById(User $actor, string $id): ?Salary
    {
        $this->ensureAdmin($actor);

        return $this->salaryRepository->findById($id);
    }

    public function createSalary(User $actor, array $payload): Salary
    {
        $this->ensureAdmin($actor);

        $employee = $this->salaryRepository->findEmployeeById((string) $payload['employee_id']);
        if ($employee === null) {
            throw new RuntimeException('The selected employee_id is invalid.');
        }

        if ($this->salaryRepository->existsByEmployeeId((string) $payload['employee_id'])) {
            throw new SalaryAlreadyExistsException;
        }

        $baseSalary = $this->resolveBaseSalaryFromPayload($payload);
        $bonus = (float) ($payload['bonus'] ?? 0);
        $deduction = $this->resolveDeductionFromPayload($payload, 0.0);

        $salary = $this->salaryRepository->create([
            'employee_id' => (string) $payload['employee_id'],
            'base_salary' => $baseSalary,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_salary' => $this->calculateNetSalary($baseSalary, $bonus, $deduction),
            'effective_from' => now()->toDateString(),
            'currency' => 'USD',
            'created_by' => (string) $actor->id,
        ]);

        Log::info('EMS salary create', [
            'user_id' => $actor->id,
            'salary_id' => $salary->id,
            'employee_id' => $salary->employee_id,
        ]);

        return $salary;
    }

    public function updateSalary(User $actor, string $id, array $payload): ?Salary
    {
        $this->ensureAdmin($actor);

        $salary = $this->salaryRepository->findById($id);
        if ($salary === null) {
            return null;
        }

        $attributes = [];

        if (array_key_exists('employee_id', $payload)) {
            $employee = $this->salaryRepository->findEmployeeById((string) $payload['employee_id']);
            if ($employee === null) {
                throw new RuntimeException('The selected employee_id is invalid.');
            }

            if ($this->salaryRepository->existsByEmployeeIdExceptSalary((string) $payload['employee_id'], $salary->id)) {
                throw new SalaryAlreadyExistsException;
            }

            $attributes['employee_id'] = (string) $payload['employee_id'];
        }

        if (array_key_exists('base_salary', $payload) || array_key_exists('amount', $payload)) {
            $attributes['base_salary'] = $this->resolveBaseSalaryFromPayload($payload);
        }

        if (array_key_exists('bonus', $payload)) {
            $attributes['bonus'] = (float) $payload['bonus'];
        }

        if (array_key_exists('deduction', $payload) || array_key_exists('deductions', $payload)) {
            $attributes['deduction'] = $this->resolveDeductionFromPayload($payload, 0.0);
        }

        $baseSalary = array_key_exists('base_salary', $attributes)
            ? (float) $attributes['base_salary']
            : (float) $salary->base_salary;
        $bonus = array_key_exists('bonus', $attributes)
            ? (float) $attributes['bonus']
            : (float) $salary->bonus;
        $deduction = array_key_exists('deduction', $attributes)
            ? (float) $attributes['deduction']
            : (float) $salary->deduction;

        $attributes['net_salary'] = $this->calculateNetSalary($baseSalary, $bonus, $deduction);

        $updatedSalary = $this->salaryRepository->update($salary, $attributes);

        Log::info('EMS salary update', [
            'user_id' => $actor->id,
            'salary_id' => $updatedSalary->id,
            'employee_id' => $updatedSalary->employee_id,
        ]);

        return $updatedSalary;
    }

    public function deleteSalary(User $actor, string $id): bool
    {
        $this->ensureAdmin($actor);

        $salary = $this->salaryRepository->findById($id);
        if ($salary === null) {
            return false;
        }

        $this->salaryRepository->delete($salary);

        return true;
    }

    private function resolveBaseSalaryFromPayload(array $payload): float
    {
        if (array_key_exists('base_salary', $payload)) {
            return (float) $payload['base_salary'];
        }

        return (float) $payload['amount'];
    }

    private function resolveDeductionFromPayload(array $payload, float $default): float
    {
        if (array_key_exists('deduction', $payload)) {
            return (float) ($payload['deduction'] ?? $default);
        }

        if (array_key_exists('deductions', $payload)) {
            return (float) ($payload['deductions'] ?? $default);
        }

        return $default;
    }

    private function calculateNetSalary(float $baseSalary, float $bonus, float $deduction): float
    {
        return $baseSalary + $bonus - $deduction;
    }
}
