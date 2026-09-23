<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create sites') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'site_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('sites', 'site_code'),
            ],
            'full_name' => ['nullable', 'string', 'max:200'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'map_link' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
