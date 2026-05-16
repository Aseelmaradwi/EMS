<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendance';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
    ];

    protected $appends = ['total_hours', 'overtime_hours'];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalHoursAttribute(): ?float
    {
        if ($this->check_in_time === null || $this->check_out_time === null) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return 0.0;
        }

        return round($checkIn->diffInSeconds($checkOut) / 3600, 2);
    }

    public function getOvertimeHoursAttribute(): float
    {
        if ($this->check_out_time === null) {
            return 0.0;
        }

        if ($this->attendance_date === null) {
            return 0.0;
        }

        $attendanceDate = Carbon::parse($this->attendance_date)->toDateString();
        $workEnd = (string) config('attendance.work_end', '17:00:00');

        $workEndAt = Carbon::parse($attendanceDate.' '.$workEnd);
        $checkOut = Carbon::parse($this->check_out_time);

        if ($checkOut->lessThanOrEqualTo($workEndAt)) {
            return 0.0;
        }

        return round($workEndAt->diffInMinutes($checkOut) / 60, 2);
    }
}
