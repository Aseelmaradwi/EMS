<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AdminAccessDeniedException;
use App\Exceptions\SalaryAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\IndexSalaryRequest;
use App\Http\Requests\Salary\StoreSalaryRequest;
use App\Http\Requests\Salary\UpdateSalaryRequest;
use App\Http\Resources\SalaryResource;
use App\Models\User;
use App\Services\SalaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class SalaryController extends Controller
{
    public function __construct(private SalaryService $salaryService) {}

    public function index(IndexSalaryRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->salaryService->listSalaries($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Salaries fetched successfully.',
            [
                'salaries' => SalaryResource::collection($paginator->items()),
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
            $salary = $this->salaryService->getSalaryById($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($salary === null) {
            return ApiResponse::error('Salary not found.', [], 'SALARY_NOT_FOUND', 404);
        }

        return ApiResponse::success('Salary fetched successfully.', [
            'salary' => new SalaryResource($salary),
        ], []);
    }

    public function store(StoreSalaryRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $salary = $this->salaryService->createSalary($actor, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        } catch (SalaryAlreadyExistsException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'SALARY_ALREADY_EXISTS', 409);
        } catch (RuntimeException $exception) {
            return ApiResponse::error('Validation failed.', ['employee_id' => [$exception->getMessage()]], 'VALIDATION_ERROR', 422);
        }

        return ApiResponse::success('Salary created successfully.', [
            'salary' => new SalaryResource($salary),
        ], [], 201);
    }

    public function update(UpdateSalaryRequest $request, string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $salary = $this->salaryService->updateSalary($actor, $id, $request->validated());
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        } catch (SalaryAlreadyExistsException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'SALARY_ALREADY_EXISTS', 409);
        } catch (RuntimeException $exception) {
            return ApiResponse::error('Validation failed.', ['employee_id' => [$exception->getMessage()]], 'VALIDATION_ERROR', 422);
        }

        if ($salary === null) {
            return ApiResponse::error('Salary not found.', [], 'SALARY_NOT_FOUND', 404);
        }

        return ApiResponse::success('Salary updated successfully.', [
            'salary' => new SalaryResource($salary),
        ], []);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $deleted = $this->salaryService->deleteSalary($actor, $id);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        if (! $deleted) {
            return ApiResponse::error('Salary not found.', [], 'SALARY_NOT_FOUND', 404);
        }

        return ApiResponse::success('Salary deleted successfully.', [], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
