<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use App\Repositories\DepartmentRepository;
use App\Services\Concerns\EnsuresAdminAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    use EnsuresAdminAccess;

    public function __construct(private DepartmentRepository $departmentRepository) {}

    public function listDepartments(User $actor, array $filters): LengthAwarePaginator
    {
        $this->ensureAdmin($actor);

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        return $this->departmentRepository->paginateDepartments($filters, $perPage);
    }

    public function getDepartmentById(User $actor, string $id): ?Department
    {
        $this->ensureAdmin($actor);

        return $this->departmentRepository->findById($id);
    }

    public function createDepartment(User $actor, array $payload): Department
    {
        $this->ensureAdmin($actor);

        return $this->departmentRepository->create([
            'name' => $payload['name'],
            'status' => 'active',
            'manager_id' => $payload['manager_id'] ?? null,
        ]);
    }

    public function updateDepartment(User $actor, string $id, array $payload): ?Department
    {
        $this->ensureAdmin($actor);

        $department = $this->departmentRepository->findById($id);
        if ($department === null) {
            return null;
        }

        $attributes = [];
        if (array_key_exists('name', $payload)) {
            $attributes['name'] = $payload['name'];
        }
        if (array_key_exists('manager_id', $payload)) {
            $attributes['manager_id'] = $payload['manager_id'];
        }

        return $this->departmentRepository->update($department, $attributes);
    }

    public function deleteDepartment(User $actor, string $id): bool
    {
        $this->ensureAdmin($actor);

        $department = $this->departmentRepository->findById($id);
        if ($department === null) {
            return false;
        }

        $this->departmentRepository->delete($department);

        return true;
    }
}
