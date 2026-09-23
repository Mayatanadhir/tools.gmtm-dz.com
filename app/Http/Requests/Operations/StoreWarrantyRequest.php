<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\WarrantyStatus;
use App\Enums\WarrantyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create warranties') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'reference' => [
                'required',
                'string',
                'max:100',
                Rule::unique('garanties', 'reference'),
            ],
            'bank_name' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'started_at' => ['nullable', 'date'],
            'status' => ['required', new Enum(WarrantyStatus::class)],
            'type' => ['required', new Enum(WarrantyType::class)],
        ];
    }
}
