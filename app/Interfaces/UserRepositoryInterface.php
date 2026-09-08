<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update a user's password.
     */
    public function updatePassword(User $user, string $newHashedPassword): bool;

    /**
     * Get paginated verified users.
     */
    public function getVerifiedUsers(int $perPage = 15): LengthAwarePaginator;
}
