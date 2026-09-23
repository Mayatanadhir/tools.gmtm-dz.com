<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view employees');
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $user->can('view employees');
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->can('create employees');
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $user->can('edit employees');
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('delete employees');
    }

    /**
     * Determine whether the user can view or manage employee financial compensation (salary and daily rate).
     */
    public function viewCompensation(User $user, ?Employee $employee = null): bool
    {
        return $user->can('view employee compensation');
    }
}
