<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Retrieve all records.
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by its identifier.
     *
     * @param  array<int, string>  $columns
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by its identifier or throw an exception.
     *
     * @param  array<int, string>  $columns
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * Create a new record.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $id, array $attributes): bool;

    /**
     * Delete a record by its identifier.
     */
    public function delete(int|string $id): bool;

    /**
     * Paginate records.
     *
     * @param  array<int, string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Retrieve filtered records using dynamic scopeFilter.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function filter(array $filters = [], array $columns = ['*']): Collection;

    /**
     * Paginate filtered records using dynamic scopeFilter.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
