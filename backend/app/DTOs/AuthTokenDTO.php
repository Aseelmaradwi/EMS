<?php

namespace App\DTOs;

use App\Models\User;

class AuthTokenDTO
{
    /**
     * Create a new DTO instance.
     */
    public function __construct(
        public User $user,
        public string $token,
        public string $tokenType,
        public int $expiresIn
    ) {}
}
