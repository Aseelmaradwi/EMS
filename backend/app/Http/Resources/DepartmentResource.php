<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'name' => $this->name,
            'status' => $this->status,
            'manager_id' => $this->manager_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'manager' => [
                'id' => $this->manager?->id,
                'name' => $this->manager?->name,
                'email' => $this->manager?->email,
                'role' => [
                    'id' => $this->manager?->role?->id,
                    'name' => $this->manager?->role?->name,
                ],
            ],
        ];
    }
}
