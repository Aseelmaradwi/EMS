<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginateUsers(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->baseUsersQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?User
    {
        return User::query()->with(['role', 'employee'])->find($id);
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes)->load('role');
    }

    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->load('role');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function findRoleByName(string $name): ?Role
    {
        return Role::query()->where('name', $name)->first();
    }

    public function findRoleById(string $id): ?Role
    {
        return Role::query()->find($id);
    }

    private function baseUsersQuery(array $filters)
    {
        return User::query()
            ->with('role')
            ->when(isset($filters['search']) && $filters['search'] !== '', function ($query) use ($filters): void {
                $search = (string) $filters['search'];
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['role_id']) && $filters['role_id'] !== '', function ($query) use ($filters): void {
                $query->where('role_id', (string) $filters['role_id']);
            })
            ->when(isset($filters['role']) && $filters['role'] !== '', function ($query) use ($filters): void {
                $query->whereHas('role', function ($roleQuery) use ($filters): void {
                    $roleQuery->where('name', (string) $filters['role']);
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($query) use ($filters): void {
                $query->where('status', (string) $filters['status']);
            })
            ->orderBy('created_at', 'desc');
    }
}
