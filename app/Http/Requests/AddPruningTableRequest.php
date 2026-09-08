<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataPruningService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddPruningTableRequest extends FormRequest
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
            'table' => ['required', 'string', 'max:64'],
            'primary_key' => ['required', 'string', 'max:64'],
            'date_column' => ['required', 'string', 'max:64'],
            'retention_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'max_records' => ['required', 'integer', 'min:0', 'max:10000000'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tableName = (string) $this->input('table');
            if ($tableName !== '') {
                /** @var DataPruningService $pruningService */
                $pruningService = app(DataPruningService::class);
                if ($pruningService->isTableProtected($tableName)) {
                    $validator->errors()->add('table', __('Table :table is sovereign and cannot be onboarded to automated pruning.', ['table' => $tableName]));
                }
            }
        });
    }
}
