<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Equipment;
use Illuminate\Pagination\LengthAwarePaginator;

interface EquipmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate filtered equipment with relations eagerly loaded.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $columns
     */
    public function paginateWithFilter(array $filters = [], int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find equipment by serial number.
     */
    public function findBySerialNumber(string $serialNumber): ?Equipment;

    /**
     * Find equipment by internal inventory code.
     */
    public function findByInternalCode(string $internalCode): ?Equipment;
}
