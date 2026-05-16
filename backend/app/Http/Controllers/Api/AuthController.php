<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthUserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $authTokenDTO = $this->authService->register($request->validated());
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'AUTH_CONFIGURATION_ERROR', 500);
        }

        return ApiResponse::success(
            'User registered successfully.',
            [
                'user' => new AuthUserResource($authTokenDTO->user),
                'token' => $authTokenDTO->token,
            ],
            [
                'token_type' => $authTokenDTO->tokenType,
                'expires_in' => $authTokenDTO->expiresIn,
            ],
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $authTokenDTO = $this->authService->login($request->validated());

        if ($authTokenDTO === null) {
            return ApiResponse::error(
                'Invalid credentials.',
                ['email' => ['The provided credentials are incorrect or account is inactive.']],
                'AUTH_INVALID_CREDENTIALS',
                401
            );
        }

        return ApiResponse::success(
            'Login successful.',
            [
                'user' => new AuthUserResource($authTokenDTO->user),
                'token' => $authTokenDTO->token,
            ],
            [
                'token_type' => $authTokenDTO->tokenType,
                'expires_in' => $authTokenDTO->expiresIn,
            ]
        );
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
        } catch (RuntimeException) {
            return ApiResponse::error('Invalid or expired token', [], 'AUTH_INVALID_TOKEN', 401);
        }

        return ApiResponse::success('Logged out successfully', [], []);
    }

    public function refresh(): JsonResponse
    {
        try {
            $authTokenDTO = $this->authService->refresh();
        } catch (RuntimeException) {
            return ApiResponse::error('Invalid token', [], 'AUTH_INVALID_TOKEN', 401);
        }

        return ApiResponse::success(
            'Token refreshed successfully.',
            [
                'user' => new AuthUserResource($authTokenDTO->user),
                'token' => $authTokenDTO->token,
            ],
            [
                'token_type' => $authTokenDTO->tokenType,
                'expires_in' => $authTokenDTO->expiresIn,
            ]
        );
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if ($user === null) {
            return ApiResponse::error('Unauthenticated', [], 'AUTH_UNAUTHORIZED', 401);
        }

        return ApiResponse::success('Authenticated user fetched successfully.', [
            'user' => new AuthUserResource($user),
        ], []);
    }
}
