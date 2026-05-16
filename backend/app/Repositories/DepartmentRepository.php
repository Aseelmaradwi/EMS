<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentRepository
{
    public function paginateDepartments(array $filters, int $perPage): LengthAwarePaginator
    {
        return Department::query()
            ->with(['manager.role'])
            ->when(
                isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '',
                function ($query) use ($filters) {
                    $search = trim((string) $filters['search']);

                    $query->where('name', 'like', "%{$search}%");
                }
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Department
    {
        return Department::query()
            ->with(['manager.role'])
            ->find($id);
    }

    public function create(array $attributes): Department
    {
        return Department::query()
            ->create($attributes)
            ->load('manager.role');
    }

    public function update(Department $department, array $attributes): Department
    {
        $department->fill($attributes);
        $department->save();

        return $department->load('manager.role');
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}
