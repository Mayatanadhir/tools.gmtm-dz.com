<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData;

use App\Enums\EmployeePosition;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit employees') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Employee|string|int|null $employee */
        $employee = $this->route('employee');
        $employeeId = $employee instanceof Employee ? $employee->id : $employee;

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'registration_number')
                    ->ignore($employeeId)
                    ->withoutTrashed(),
            ],
            'position' => ['required', 'string', Rule::enum(EmployeePosition::class)],
            'status' => ['nullable', 'string', Rule::enum(EmployeeStatus::class)],
            'join_date' => ['required', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }
}
