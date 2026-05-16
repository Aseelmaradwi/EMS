<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AttendanceAccessDeniedException;
use App\Exceptions\AttendanceAlreadyCheckedInException;
use App\Exceptions\AttendanceAlreadyCheckedOutException;
use App\Exceptions\AttendanceCheckInRequiredException;
use App\Exceptions\AttendanceOnApprovedLeaveException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Http\Requests\Attendance\IndexAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\User;
use App\Services\AttendanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $attendance = $this->attendanceService->checkIn($actor);
        } catch (AttendanceAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (AttendanceAlreadyCheckedInException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'ATTENDANCE_ALREADY_CHECKED_IN', 422);
        } catch (AttendanceOnApprovedLeaveException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'ATTENDANCE_ON_APPROVED_LEAVE', 422);
        }

        return ApiResponse::success('Check-in recorded successfully.', [
            'attendance' => new AttendanceResource($attendance),
        ], [], 201);
    }

    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $attendance = $this->attendanceService->checkOut($actor);
        } catch (AttendanceAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        } catch (AttendanceCheckInRequiredException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'ATTENDANCE_CHECK_IN_REQUIRED', 422);
        } catch (AttendanceAlreadyCheckedOutException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 'ATTENDANCE_ALREADY_CHECKED_OUT', 422);
        }

        return ApiResponse::success('Check-out recorded successfully.', [
            'attendance' => new AttendanceResource($attendance),
        ], []);
    }

    public function index(IndexAttendanceRequest $request): JsonResponse
    {
        $actor = $this->resolveAuthenticatedUser();
        if ($actor === null) {
            return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
        }

        try {
            $paginator = $this->attendanceService->listAttendance($actor, $request->validated());
        } catch (AttendanceAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        }

        return ApiResponse::success(
            'Attendance records fetched successfully.',
            [
                'attendance' => AttendanceResource::collection($paginator->items()),
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
            $attendance = $this->attendanceService->getAttendanceById($actor, $id);
        } catch (AttendanceAccessDeniedException) {
            return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
        }

        if ($attendance === null) {
            return ApiResponse::error('Attendance record not found.', [], 'ATTENDANCE_NOT_FOUND', 404);
        }

        return ApiResponse::success('Attendance record fetched successfully.', [
            'attendance' => new AttendanceResource($attendance),
        ], []);
    }

    private function resolveAuthenticatedUser(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }
}
