<?php

namespace App\Services;

use App\DTOs\AuthTokenDTO;
use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    public function __construct(private AuthRepository $authRepository) {}

    public function register(array $payload): AuthTokenDTO
    {
        $defaultRole = $this->authRepository->findDefaultRole();

        if ($defaultRole === null) {
            throw new RuntimeException('Default employee role is missing.');
        }

        $user = $this->authRepository->createUser([
            'role_id' => $defaultRole->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $payload['password'],
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);
        $freshUser = $this->authRepository->findUserById($user->id) ?? $user;

        Log::info('User registration completed', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $this->buildTokenDTO($freshUser, $token);
    }

    public function login(array $credentials): ?AuthTokenDTO
    {
        /** @var JWTGuard $apiGuard */
        $apiGuard = auth('api');

        $token = $apiGuard->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ]);

        if (! is_string($token) || $token === '') {
            return null;
        }

        $user = $apiGuard->user();
        if (! $user instanceof User) {
            return null;
        }

        $this->authRepository->updateLastLoginAt($user);
        $user = $this->authRepository->findUserById($user->id) ?? $user;

        Log::info('We are logged in to the EMS OSP', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'role' => $user?->role?->name,
            'logged_at' => now()->toIso8601String(),
        ]);

        if ($user === null) {
            return null;
        }

        return $this->buildTokenDTO($user, $token);
    }

    public function logout(): void
    {
        /** @var JWTGuard $apiGuard */
        $apiGuard = auth('api');

        $user = $apiGuard->user();

        try {
            $apiGuard->logout();
        } catch (\Throwable) {
            throw new RuntimeException('Invalid or expired token');
        }

        Log::info('User logout completed', [
            'user_id' => $user?->id,
            'email' => $user?->email,
        ]);
    }

    public function refresh(): AuthTokenDTO
    {
        /** @var JWTGuard $apiGuard */
        $apiGuard = auth('api');

        $user = $apiGuard->user();
        if (! $user instanceof User) {
            throw new RuntimeException('Invalid token');
        }

        try {
            $token = $apiGuard->refresh();
        } catch (\Throwable) {
            throw new RuntimeException('Invalid token');
        }

        $user = $this->authRepository->findUserById($user->id) ?? $user;

        return $this->buildTokenDTO($user, $token);
    }

    public function me(): ?User
    {
        try {
            /** @var JWTGuard $apiGuard */
            $apiGuard = auth('api');

            $user = $apiGuard->user();
        } catch (\Throwable) {
            return null;
        }

        if (! $user instanceof User) {
            return null;
        }

        return $this->authRepository->findUserById($user->id) ?? $user;
    }

    private function buildTokenDTO(User $user, string $token): AuthTokenDTO
    {
        return new AuthTokenDTO(
            user: $user,
            token: $token,
            tokenType: 'Bearer',
            expiresIn: JWTAuth::factory()->getTTL() * 60,
        );
    }
}
