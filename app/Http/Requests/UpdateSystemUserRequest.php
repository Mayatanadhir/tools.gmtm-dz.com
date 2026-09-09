<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateSystemUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User|string|null $user */
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role' => ['sometimes', 'required', 'string', 'exists:roles,name'],
            'status' => ['nullable', 'string', Rule::enum(AccountStatus::class)],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
            'profile_photo_path' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
