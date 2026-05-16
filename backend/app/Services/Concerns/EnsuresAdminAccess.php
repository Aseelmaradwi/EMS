<?php

namespace App\Services\Concerns;

use App\Exceptions\AdminAccessDeniedException;
use App\Models\User;

trait EnsuresAdminAccess
{
    protected function ensureAdmin(User $actor): void
    {
        $actor->loadMissing('role');

        if ($actor->role?->name !== 'admin') {
            throw new AdminAccessDeniedException;
        }
    }
}
