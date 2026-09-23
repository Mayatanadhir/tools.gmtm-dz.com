<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find an employee by registration number.
     */
    public function findByRegistrationNumber(string $registrationNumber): ?Employee;

    /**
     * Paginate filtered employees with user relationship eagerly loaded.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
