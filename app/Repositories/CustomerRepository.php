<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a customer by reference code.
     */
    public function findByReference(string $reference): ?Customer
    {
        /** @var Customer|null */
        return $this->model->newQuery()->where('reference', $reference)->first();
    }
}
