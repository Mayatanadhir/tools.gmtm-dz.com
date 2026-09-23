<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\SiteRepositoryInterface;
use App\Models\Site;
use Illuminate\Pagination\LengthAwarePaginator;

class SiteRepository extends BaseRepository implements SiteRepositoryInterface
{
    public function __construct(Site $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate filtered sites with eager-loaded customer relationship.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('customer');

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage, $columns)->withQueryString();
    }

    /**
     * Find a site by its site code.
     */
    public function findByCode(string $code): ?Site
    {
        /** @var Site|null */
        return $this->model->newQuery()->where('site_code', $code)->first();
    }
}
