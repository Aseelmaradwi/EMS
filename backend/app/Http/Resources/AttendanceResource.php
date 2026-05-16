<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->attendance_date,
            'check_in_at' => $this->check_in_time,
            'check_out_at' => $this->check_out_time,
            'status' => $this->status,
            'total_hours' => $this->total_hours,
            'overtime_hours' => $this->overtime_hours,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'employee' => [
                'id' => $this->employee?->id,
                'employee_code' => $this->employee?->employee_code,
                'employment_status' => $this->employee?->employment_status,
                'user' => [
                    'id' => $this->employee?->user?->id,
                    'name' => $this->employee?->user?->name,
                    'email' => $this->employee?->user?->email,
                    'role' => [
                        'id' => $this->employee?->user?->role?->id,
                        'name' => $this->employee?->user?->role?->name,
                    ],
                ],
                'department' => [
                    'id' => $this->employee?->department?->id,
                    'name' => $this->employee?->department?->name,
                ],
            ],
        ];
    }
}
