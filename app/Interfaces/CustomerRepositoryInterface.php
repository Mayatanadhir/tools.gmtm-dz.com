<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Customer;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a customer by reference code.
     */
    public function findByReference(string $reference): ?Customer;
}
