<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\EquipmentRepositoryInterface;
use App\Models\Equipment;
use Illuminate\Pagination\LengthAwarePaginator;

class EquipmentRepository extends BaseRepository implements EquipmentRepositoryInterface
{
    public function __construct(Equipment $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate filtered equipment with specifications and grandeurs eagerly loaded.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['specifications.grandeur']);

        if (method_exists($this->model, 'scopeFilter')) {
            $query->filter($filters);
        }

        return $query->latest('id')->paginate($perPage, $columns)->withQueryString();
    }

    /**
     * Find equipment by serial number.
     */
    public function findBySerialNumber(string $serialNumber): ?Equipment
    {
        /** @var Equipment|null */
        return $this->model->newQuery()->where('serial_number', $serialNumber)->first();
    }

    /**
     * Find equipment by internal inventory code.
     */
    public function findByInternalCode(string $internalCode): ?Equipment
    {
        /** @var Equipment|null */
        return $this->model->newQuery()->where('internal_code', $internalCode)->first();
    }
}
