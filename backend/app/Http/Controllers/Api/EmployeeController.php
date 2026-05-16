<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AdminAccessDeniedException;
use App\Exceptions\EmployeeAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\User;
use App\Services\EmployeeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(IndexEmployeeRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->employeeService->listEmployees($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Employees fetched successfully.',
            [
                'employees' => EmployeeResource::collection($paginator->items()),
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
            $employee = $this->employeeService->getEmployeeById($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($employee === null) {
            return ApiResponse::error('Employee not found.', [], 'EMPLOYEE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Employee fetched successfully.', [
            'employee' => new EmployeeResource($employee),
        ], []);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $employee = $this->employeeService->createEmployee($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        } catch (EmployeeAlreadyExistsException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'EMPLOYEE_ALREADY_EXISTS', 409);
        } catch (RuntimeException $exception) {
            return ApiResponse::error('Validation failed.', ['user_id' => [$exception->getMessage()]], 'VALIDATION_ERROR', 422);
        }

        return ApiResponse::success('Employee created successfully.', [
            'employee' => new EmployeeResource($employee),
        ], [], 201);
    }

    public function update(UpdateEmployeeRequest $request, string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $employee = $this->employeeService->updateEmployee($actor, $id, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        } catch (EmployeeAlreadyExistsException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'EMPLOYEE_ALREADY_EXISTS', 409);
        } catch (RuntimeException $exception) {
            return ApiResponse::error('Validation failed.', ['user_id' => [$exception->getMessage()]], 'VALIDATION_ERROR', 422);
        }

        if ($employee === null) {
            return ApiResponse::error('Employee not found.', [], 'EMPLOYEE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Employee updated successfully.', [
            'employee' => new EmployeeResource($employee),
        ], []);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $deleted = $this->employeeService->deleteEmployee($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if (! $deleted) {
            return ApiResponse::error('Employee not found.', [], 'EMPLOYEE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Employee deleted successfully.', [], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
