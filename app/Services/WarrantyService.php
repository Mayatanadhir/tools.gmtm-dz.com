<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\WarrantyRepositoryInterface;
use App\Models\Warranty;

class WarrantyService extends BaseService
{
    public function __construct(
        protected WarrantyRepositoryInterface $warrantyRepository
    ) {}

    /**
     * Create a new bank guarantee record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWarranty(array $data): Warranty
    {
        return $this->executeInTransaction(function () use ($data): Warranty {
            /** @var Warranty */
            return $this->warrantyRepository->create($data);
        });
    }

    /**
     * Update an existing bank guarantee record.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateWarranty(Warranty $warranty, array $data): Warranty
    {
        return $this->executeInTransaction(function () use ($warranty, $data): Warranty {
            $this->warrantyRepository->update($warranty->id, $data);

            return $warranty->refresh();
        });
    }

    /**
     * Delete a bank guarantee record.
     */
    public function deleteWarranty(Warranty $warranty): bool
    {
        return $this->executeInTransaction(function () use ($warranty): bool {
            return $this->warrantyRepository->delete($warranty->id);
        });
    }
}
