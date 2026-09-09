<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class InitialSystemSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        try {
            if (! Schema::hasTable('users')) {
                return true;
            }

            return User::count() === 0;
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $emailRules = ['required', 'string', 'lowercase', 'email', 'max:255'];
        if (Schema::hasTable('users')) {
            $emailRules[] = 'unique:'.User::class;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }
}
