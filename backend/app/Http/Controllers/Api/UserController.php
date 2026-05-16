<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AdminAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(IndexUserRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->userService->listUsers($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Users fetched successfully.',
            [
                'users' => UserResource::collection($paginator->items()),
            ],
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $user = $this->userService->getUserById($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($user === null) {
            return ApiResponse::error('User not found.', [], 'USER_NOT_FOUND', 404);
        }

        return ApiResponse::success('User fetched successfully.', [
            'user' => new UserResource($user),
        ], []);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $user = $this->userService->createUser($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('User created successfully.', [
            'user' => new UserResource($user),
        ], [], 201);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $user = $this->userService->updateUser($actor, $id, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($user === null) {
            return ApiResponse::error('User not found.', [], 'USER_NOT_FOUND', 404);
        }

        return ApiResponse::success('User updated successfully.', [
            'user' => new UserResource($user),
        ], []);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $deleted = $this->userService->deleteUser($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if (! $deleted) {
            return ApiResponse::error('User not found.', [], 'USER_NOT_FOUND', 404);
        }

        return ApiResponse::success('User deleted successfully.', [], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
