<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    /**
     * Find an employee by registration number.
     */
    public function findByRegistrationNumber(string $registrationNumber): ?Employee
    {
        /** @var Employee|null */
        return $this->model->newQuery()->where('registration_number', $registrationNumber)->first();
    }

    /**
     * Paginate filtered employees with user relationship eagerly loaded to prevent N+1 queries.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('user');

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->paginate($perPage, $columns)->withQueryString();
    }
}
