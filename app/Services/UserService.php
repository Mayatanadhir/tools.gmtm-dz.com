<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    /**
     * Get a user by ID.
     */
    public function getUserById(int|string $id): ?User
    {
        /** @var User|null */
        return $this->userRepository->find($id);
    }

    /**
     * Get a user by email.
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Register a new user with hashed password inside a database transaction.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return $this->executeInTransaction(function () use ($data): User {
            $data['password'] = Hash::make($data['password']);

            /** @var User */
            return $this->userRepository->create($data);
        });
    }

    /**
     * Update user profile attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): bool
    {
        return $this->executeInTransaction(function () use ($user, $data): bool {
            $user->fill($data);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            return $user->save();
        });
    }

    /**
     * Change user password securely.
     */
    public function changePassword(User $user, string $newPlainPassword): bool
    {
        return $this->executeInTransaction(function () use ($user, $newPlainPassword): bool {
            return $this->userRepository->updatePassword($user, Hash::make($newPlainPassword));
        });
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(User $user): bool
    {
        return $this->executeInTransaction(function () use ($user): bool {
            return $this->userRepository->delete($user->id);
        });
    }

    /**
     * Paginate users.
     */
    public function listUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }
}
