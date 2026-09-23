<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\WarrantyStatus;
use App\Enums\WarrantyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit warranties') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $warrantyId = $this->route('warranty');

        return [
            'reference' => [
                'required',
                'string',
                'max:100',
                Rule::unique('garanties', 'reference')->ignore($warrantyId),
            ],
            'bank_name' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'started_at' => ['nullable', 'date'],
            'status' => ['required', new Enum(WarrantyStatus::class)],
            'type' => ['required', new Enum(WarrantyType::class)],
        ];
    }
}
