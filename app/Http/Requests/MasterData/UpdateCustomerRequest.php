<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit clients') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Customer|string|int|null $customer */
        $customer = $this->route('customer');
        $customerId = $customer instanceof Customer ? $customer->id : $customer;

        return [
            'reference' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'reference')->ignore($customerId),
            ],
            'company_name' => ['required', 'string', 'max:200'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:200'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
