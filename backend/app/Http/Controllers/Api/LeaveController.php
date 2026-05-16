<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\EmployeeProfileNotFoundException;
use App\Exceptions\LeaveAccessDeniedException;
use App\Exceptions\LeaveNotPendingException;
use App\Exceptions\LeaveOverlapException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\IndexLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Http\Requests\Leave\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\User;
use App\Services\LeaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService) {}

    public function index(IndexLeaveRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->leaveService->listLeaves($actor, $request->validated());
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Leave requests fetched successfully.',
            [
                'leaves' => LeaveResource::collection($paginator->items()),
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
            $leave = $this->leaveService->getLeaveById($actor, $id);
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($leave === null) {
            return ApiResponse::error('Leave request not found.', [], 'LEAVE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Leave request fetched successfully.', [
            'leave' => new LeaveResource($leave),
        ], []);
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $leave = $this->leaveService->createLeave($actor, $request->validated());
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (EmployeeProfileNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'EMPLOYEE_PROFILE_NOT_FOUND', 422);
        } catch (LeaveOverlapException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'LEAVE_OVERLAP', 422);
        }

        return ApiResponse::success('Leave request created successfully.', [
            'leave' => new LeaveResource($leave),
        ], [], 201);
    }

    public function update(UpdateLeaveRequest $request, string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $leave = $this->leaveService->updateLeave($actor, $id, $request->validated());
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (LeaveNotPendingException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'LEAVE_NOT_PENDING', 422);
        } catch (LeaveOverlapException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'LEAVE_OVERLAP', 422);
        }

        if ($leave === null) {
            return ApiResponse::error('Leave request not found.', [], 'LEAVE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Leave request updated successfully.', [
            'leave' => new LeaveResource($leave),
        ], []);
    }

    public function approve(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $leave = $this->leaveService->approveLeave($actor, $id);
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (LeaveNotPendingException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'LEAVE_NOT_PENDING', 422);
        }

        if ($leave === null) {
            return ApiResponse::error('Leave request not found.', [], 'LEAVE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Leave request approved successfully.', [
            'leave' => new LeaveResource($leave),
        ], []);
    }

    public function reject(string $id): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $leave = $this->leaveService->rejectLeave($actor, $id);
        } catch (LeaveAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (LeaveNotPendingException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'LEAVE_NOT_PENDING', 422);
        }

        if ($leave === null) {
            return ApiResponse::error('Leave request not found.', [], 'LEAVE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Leave request rejected successfully.', [
            'leave' => new LeaveResource($leave),
        ], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
