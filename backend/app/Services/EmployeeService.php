<?php

namespace App\Services;

use App\Exceptions\EmployeeAlreadyExistsException;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Services\Concerns\EnsuresAdminAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use RuntimeException;

class EmployeeService
{
    use EnsuresAdminAccess;

    private const ALLOWED_EMPLOYEE_PROFILE_ROLES = ['employee', 'manager'];

    public function __construct(private EmployeeRepository $employeeRepository) {}

    public function listEmployees(User $actor, array $filters): LengthAwarePaginator
    {
        $this->ensureAdmin($actor);

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $this->employeeRepository->paginateEmployees($filters, $perPage);
    }

    public function getEmployeeById(User $actor, string $id): ?Employee
    {
        $this->ensureAdmin($actor);

        return $this->employeeRepository->findById($id);
    }

    public function createEmployee(User $actor, array $payload): Employee
    {
        $this->ensureAdmin($actor);

        $user = $this->employeeRepository->findUserByIdWithRole((string) $payload['user_id']);
        if ($user === null || ! in_array((string) $user->role?->name, self::ALLOWED_EMPLOYEE_PROFILE_ROLES, true)) {
            throw new RuntimeException('The selected user_id must belong to a user with employee or manager role.');
        }

        $department = $this->employeeRepository->findDepartmentById((string) $payload['department_id']);
        if ($department === null) {
            throw new RuntimeException('The selected department_id is invalid.');
        }

        if ($this->employeeRepository->existsByUserId((string) $payload['user_id'])) {
            throw new EmployeeAlreadyExistsException;
        }

        [$firstName, $lastName] = $this->splitFullName((string) $user->name);

        return $this->employeeRepository->create([
            'user_id' => (string) $payload['user_id'],
            'department_id' => (string) $payload['department_id'],
            'phone' => $payload['phone'] ?? null,
            'address' => $payload['address'] ?? null,
            'employee_code' => $this->generateEmployeeCode(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => (string) $user->email,
            'hire_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);
    }

    public function updateEmployee(User $actor, string $id, array $payload): ?Employee
    {
        $this->ensureAdmin($actor);

        $employee = $this->employeeRepository->findById($id);
        if ($employee === null) {
            return null;
        }

        $attributes = [];

        if (array_key_exists('user_id', $payload)) {
            $user = $this->employeeRepository->findUserByIdWithRole((string) $payload['user_id']);
            if ($user === null || ! in_array((string) $user->role?->name, self::ALLOWED_EMPLOYEE_PROFILE_ROLES, true)) {
                throw new RuntimeException('The selected user_id must belong to a user with employee or manager role.');
            }

            if ($this->employeeRepository->existsByUserIdExceptEmployee((string) $payload['user_id'], $employee->id)) {
                throw new EmployeeAlreadyExistsException;
            }

            [$firstName, $lastName] = $this->splitFullName((string) $user->name);

            $attributes['user_id'] = (string) $payload['user_id'];
            $attributes['first_name'] = $firstName;
            $attributes['last_name'] = $lastName;
            $attributes['email'] = (string) $user->email;
        }

        if (array_key_exists('department_id', $payload)) {
            $department = $this->employeeRepository->findDepartmentById((string) $payload['department_id']);
            if ($department === null) {
                throw new RuntimeException('The selected department_id is invalid.');
            }

            $attributes['department_id'] = (string) $payload['department_id'];
        }

        if (array_key_exists('phone', $payload)) {
            $attributes['phone'] = $payload['phone'];
        }

        if (array_key_exists('address', $payload)) {
            $attributes['address'] = $payload['address'];
        }

        return $this->employeeRepository->update($employee, $attributes);
    }

    public function deleteEmployee(User $actor, string $id): bool
    {
        $this->ensureAdmin($actor);

        $employee = $this->employeeRepository->findById($id);
        if ($employee === null) {
            return false;
        }

        $this->employeeRepository->delete($employee);

        return true;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if (count($parts) === 0) {
            return ['Employee', 'User'];
        }

        if (count($parts) === 1) {
            return [$parts[0], 'User'];
        }

        $firstName = array_shift($parts) ?: 'Employee';
        $lastName = implode(' ', $parts);

        return [$firstName, $lastName !== '' ? $lastName : 'User'];
    }

    private function generateEmployeeCode(): string
    {
        do {
            $candidate = 'EMP-'.Str::upper(Str::random(8));
        } while ($this->employeeRepository->existsByEmployeeCode($candidate));

        return $candidate;
    }
}
