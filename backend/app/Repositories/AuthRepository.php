<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;

class AuthRepository
{
    public function findDefaultRole(): ?Role
    {
        return Role::query()->where('name', 'employee')->first();
    }

    public function findActiveUserByEmail(string $email): ?User
    {
        return User::query()
            ->with('role')
            ->where('email', $email)
            ->where('status', 'active')
            ->first();
    }

    public function createUser(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function findUserById(string $id): ?User
    {
        return User::query()->with('role')->find($id);
    }

    public function updateLastLoginAt(User $user): void
    {
        $user->forceFill(['last_login_at' => Carbon::now()])->save();
    }
}
