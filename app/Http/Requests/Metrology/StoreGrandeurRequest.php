<?php

declare(strict_types=1);

namespace App\Http\Requests\Metrology;

use App\Enums\GrandeurType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrandeurRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create quantities units') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', Rule::enum(GrandeurType::class)],
        ];
    }
}
