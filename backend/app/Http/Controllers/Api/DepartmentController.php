<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AdminAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\IndexDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\User;
use App\Services\DepartmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $departmentService) {}

    public function index(IndexDepartmentRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->departmentService->listDepartments($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Departments fetched successfully.',
            [
                'departments' => DepartmentResource::collection($paginator->items()),
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
            $department = $this->departmentService->getDepartmentById($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($department === null) {
            return ApiResponse::error('Department not found.', [], 'DEPARTMENT_NOT_FOUND', 404);
        }

        return ApiResponse::success('Department fetched successfully.', [
            'department' => new DepartmentResource($department),
        ], []);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $department = $this->departmentService->createDepartment($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('Department created successfully.', [
            'department' => new DepartmentResource($department),
        ], [], 201);
    }

    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $department = $this->departmentService->updateDepartment($actor, $id, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($department === null) {
            return ApiResponse::error('Department not found.', [], 'DEPARTMENT_NOT_FOUND', 404);
        }

        return ApiResponse::success('Department updated successfully.', [
            'department' => new DepartmentResource($department),
        ], []);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $deleted = $this->departmentService->deleteDepartment($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if (! $deleted) {
            return ApiResponse::error('Department not found.', [], 'DEPARTMENT_NOT_FOUND', 404);
        }

        return ApiResponse::success('Department deleted successfully.', [], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
