<?php

namespace App\Jobs;

use App\Services\AttendanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MarkEmployeesAbsentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AttendanceService $attendanceService): void
    {
        $date = now()->toDateString();

        Log::info('EMS auto absent job started', [
            'attendance_date' => $date,
        ]);

        $createdAbsentCount = $attendanceService->markAbsentForDate($date);

        Log::info('EMS auto absent job completed', [
            'attendance_date' => $date,
            'created_absent_records' => $createdAbsentCount,
        ]);
    }
}
