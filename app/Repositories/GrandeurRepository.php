<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\GrandeurType;
use App\Interfaces\GrandeurRepositoryInterface;
use App\Models\Grandeur;
use Illuminate\Pagination\LengthAwarePaginator;

class GrandeurRepository extends BaseRepository implements GrandeurRepositoryInterface
{
    public function __construct(Grandeur $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate filtered quantities with specifications count.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->withCount('specifications');

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->latest('id')->paginate($perPage, $columns)->withQueryString();
    }

    /**
     * Check if a quantity is linked to any equipment or instrument specifications.
     */
    public function isLinkedToSpecifications(int $grandeurId): bool
    {
        /** @var Grandeur|null $grandeur */
        $grandeur = $this->model->newQuery()->find($grandeurId);

        if (! $grandeur) {
            return false;
        }

        return $grandeur->specifications()->exists();
    }

    /**
     * Count quantities by operation type.
     */
    public function countByType(GrandeurType $type): int
    {
        return $this->model->newQuery()->where('type', $type)->count();
    }
}
