<?php

namespace App\Http\Requests\Employee;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
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
        $assignableRoleIds = Role::query()
            ->whereIn('name', ['employee', 'manager'])
            ->pluck('id')
            ->all();

        return [
            'user_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($assignableRoleIds): void {
                    $query->whereIn('role_id', $assignableRoleIds);
                }),
            ],
            'department_id' => ['required', 'uuid', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ];
    }
}
