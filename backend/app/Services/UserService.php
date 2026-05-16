<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Concerns\EnsuresAdminAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class UserService
{
    use EnsuresAdminAccess;

    private const ALLOWED_ASSIGNABLE_ROLES = ['admin', 'manager', 'employee'];

    public function __construct(private UserRepository $userRepository) {}

    public function listUsers(User $actor, array $filters): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $perPage = max(1, min($perPage, 100));

        $this->ensureAdmin($actor);

        return $this->userRepository->paginateUsers($filters, $perPage);
    }

    public function getUserById(User $actor, string $id): ?User
    {
        $this->ensureAdmin($actor);

        return $this->userRepository->findById($id);
    }

    public function createUser(User $actor, array $payload): User
    {
        $this->ensureAdmin($actor);

        $resolvedRoleId = $this->resolveRoleIdForCreate($payload);

        $user = $this->userRepository->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => bcrypt($payload['password']),
            'role_id' => $resolvedRoleId,
            'status' => $payload['status'],
        ]);

        Log::info('EMS user created', [
            'actor_user_id' => $actor->id,
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
        ]);

        return $user;
    }

    public function updateUser(User $actor, string $id, array $payload): ?User
    {
        $this->ensureAdmin($actor);

        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return null;
        }

        $attributes = [];
        if (array_key_exists('name', $payload)) {
            $attributes['name'] = $payload['name'];
        }
        if (array_key_exists('email', $payload)) {
            $attributes['email'] = $payload['email'];
        }
        if (array_key_exists('role', $payload) || array_key_exists('role_id', $payload)) {
            $attributes['role_id'] = $this->resolveRoleIdFromPayload($payload);
        }
        if (array_key_exists('status', $payload)) {
            $attributes['status'] = $payload['status'];
        }
        if (array_key_exists('password', $payload) && $payload['password'] !== null && $payload['password'] !== '') {
            $attributes['password'] = bcrypt($payload['password']);
        }

        $updatedUser = $this->userRepository->update($user, $attributes);

        Log::info('EMS user updated', [
            'actor_user_id' => $actor->id,
            'target_user_id' => $updatedUser->id,
            'target_user_email' => $updatedUser->email,
        ]);

        return $updatedUser;
    }

    public function deleteUser(User $actor, string $id): bool
    {
        $this->ensureAdmin($actor);

        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return false;
        }

        $this->userRepository->delete($user);

        Log::info('EMS user deleted', [
            'actor_user_id' => $actor->id,
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
        ]);

        return true;
    }

    private function resolveRoleIdForCreate(array $payload): string
    {
        if (! array_key_exists('role', $payload) && ! array_key_exists('role_id', $payload)) {
            $defaultRole = $this->userRepository->findRoleByName('employee');
            if ($defaultRole === null) {
                throw new RuntimeException('Role not found.');
            }

            return (string) $defaultRole->id;
        }

        return $this->resolveRoleIdFromPayload($payload);
    }

    private function resolveRoleIdFromPayload(array $payload): string
    {
        if (array_key_exists('role', $payload) && $payload['role'] !== null && $payload['role'] !== '') {
            $role = $this->userRepository->findRoleByName((string) $payload['role']);

            if ($role === null) {
                throw new RuntimeException('Role not found.');
            }

            if (! in_array($role->name, self::ALLOWED_ASSIGNABLE_ROLES, true)) {
                throw new RuntimeException('Role not allowed.');
            }

            return (string) $role->id;
        }

        if (array_key_exists('role_id', $payload) && $payload['role_id'] !== null && $payload['role_id'] !== '') {
            $role = $this->userRepository->findRoleById((string) $payload['role_id']);

            if ($role === null) {
                throw new RuntimeException('Role not found.');
            }

            if (! in_array($role->name, self::ALLOWED_ASSIGNABLE_ROLES, true)) {
                throw new RuntimeException('Role not allowed.');
            }

            return (string) $role->id;
        }

        throw new RuntimeException('Role not found.');
    }
}
