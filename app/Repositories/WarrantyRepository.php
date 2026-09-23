<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\WarrantyRepositoryInterface;
use App\Models\Warranty;
use Illuminate\Pagination\LengthAwarePaginator;

class WarrantyRepository extends BaseRepository implements WarrantyRepositoryInterface
{
    public function __construct(Warranty $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate filtered bank guarantees ordering active records first.
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

        // Active records on top, followed by newest started_at and newest id
        $query->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('started_at')
            ->orderByDesc('id');

        return $query->paginate($perPage, $columns)->withQueryString();
    }
}
