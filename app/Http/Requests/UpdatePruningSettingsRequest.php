<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePruningSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['sometimes', 'boolean'],
            'chunk_size' => ['required', 'integer', 'min:50', 'max:10000'],
            'tables' => ['required', 'array'],
            'tables.*.enabled' => ['sometimes', 'boolean'],
            'tables.*.retention_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'tables.*.max_records' => ['required', 'integer', 'min:0', 'max:10000000'],
            'tables.notifications.only_read' => ['sometimes', 'boolean'],
        ];
    }
}
