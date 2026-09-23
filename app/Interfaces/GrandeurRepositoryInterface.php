<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Enums\GrandeurType;
use Illuminate\Pagination\LengthAwarePaginator;

interface GrandeurRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate filtered quantities with specifications count.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Check if a quantity is linked to any equipment or instrument specifications.
     */
    public function isLinkedToSpecifications(int $grandeurId): bool;

    /**
     * Count quantities by operation type.
     */
    public function countByType(GrandeurType $type): int;
}
