<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->model->newQuery()->where('email', $email)->first();
    }

    /**
     * Update a user's password.
     */
    public function updatePassword(User $user, string $newHashedPassword): bool
    {
        return $user->update(['password' => $newHashedPassword]);
    }

    /**
     * Get paginated verified users.
     */
    public function getVerifiedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereNotNull('email_verified_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
