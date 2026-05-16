<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AdminAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ReportsController extends Controller
{
    public function __construct(private ReportsService $reportsService) {}

    public function employees(Request $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $reportData = $this->reportsService->employeesReport($actor);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('Report fetched successfully', $reportData);
    }

    public function departments(Request $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $reportData = $this->reportsService->departmentsReport($actor);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('Report fetched successfully', $reportData);
    }

    public function attendance(Request $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        $validator = Validator::make($request->all(), [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors()->toArray(), 'VALIDATION_ERROR', 422);
        }

        try {
            $reportData = $this->reportsService->attendanceReport($actor, $request->only([
                'from_date',
                'to_date',
            ]));
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        } catch (RuntimeException $exception) {
            return ApiResponse::error('Validation failed.', ['to_date' => [$exception->getMessage()]], 'VALIDATION_ERROR', 422);
        }

        return ApiResponse::success('Report fetched successfully', $reportData);
    }

    public function salaries(Request $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $reportData = $this->reportsService->salariesReport($actor);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('Report fetched successfully', $reportData);
    }

    public function leaves(Request $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $reportData = $this->reportsService->leavesReport($actor);
        } catch (AdminAccessDeniedException) {
            return ApiResponse::error('Forbidden. Admin access is required.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success('Report fetched successfully', $reportData);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
