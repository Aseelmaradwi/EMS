<?php

namespace App\Http\Requests\Department;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore((string) $this->route('id')),
            ],
            'manager_id' => [
                'nullable',
                'uuid',
                'exists:users,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $isManager = User::query()
                        ->whereKey((string) $value)
                        ->whereHas('role', function ($query): void {
                            $query->where('name', 'manager');
                        })
                        ->exists();

                    if (! $isManager) {
                        $fail('The selected manager_id must belong to a manager user.');
                    }
                },
            ],
        ];
    }
}
