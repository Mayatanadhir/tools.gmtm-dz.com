<?php

declare(strict_types=1);

namespace App\Http\Requests\Metrology;

use App\Enums\AccuracyType;
use App\Enums\EquipmentCategory;
use App\Enums\EquipmentPackage;
use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit equipment') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $equipment = $this->route('equipment');
        $id = $equipment instanceof Equipment ? $equipment->id : (int) $equipment;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'internal_code' => ['nullable', 'string', 'max:100'],
            'serial_number' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('equipment', 'serial_number')->ignore($id)->withoutTrashed(),
            ],
            'category' => ['required', 'string', Rule::enum(EquipmentCategory::class)],
            'package' => ['nullable', 'string', Rule::enum(EquipmentPackage::class)],
            'status' => ['nullable', 'string', Rule::enum(EquipmentStatus::class)],
            'requires_calibration' => ['nullable', 'boolean'],
            'designation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'certificate' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'params' => ['nullable', 'array'],
            'params.*.selected' => ['nullable', 'boolean'],
            'params.*.min' => ['nullable', 'numeric'],
            'params.*.max' => ['nullable', 'numeric'],
            'params.*.acc' => ['nullable', 'numeric'],
            'params.*.acc_type' => ['nullable', 'string', Rule::enum(AccuracyType::class)],
        ];
    }
}
