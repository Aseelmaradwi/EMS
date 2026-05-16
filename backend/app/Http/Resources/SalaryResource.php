<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'base_salary' => (float) $this->base_salary,
            'amount' => $this->amount,
            'bonus' => (float) $this->bonus,
            'deduction' => (float) $this->deduction,
            'deductions' => $this->deductions,
            'net_salary' => (float) $this->net_salary,
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
