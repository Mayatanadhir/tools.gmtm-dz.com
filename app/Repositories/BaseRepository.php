<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    /**
     * Retrieve all records.
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    /**
     * Find a record by its identifier.
     *
     * @param  array<int, string>  $columns
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    /**
     * Find a record by its identifier or throw an exception.
     *
     * @param  array<int, string>  $columns
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->model->newQuery()->findOrFail($id, $columns);
    }

    /**
     * Create a new record.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * Update an existing record.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $id, array $attributes): bool
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return $record->update($attributes);
    }

    /**
     * Delete a record by its identifier.
     */
    public function delete(int|string $id): bool
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }

    /**
     * Paginate records.
     *
     * @param  array<int, string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage, $columns)->withQueryString();
    }

    /**
     * Retrieve filtered records using dynamic scopeFilter if available.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function filter(array $filters = [], array $columns = ['*']): Collection
    {
        $query = $this->model->newQuery();

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->get($columns);
    }

    /**
     * Paginate filtered records using dynamic scopeFilter if available.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->paginate($perPage, $columns)->withQueryString();
    }
}
