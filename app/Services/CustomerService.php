<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerService extends BaseService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    /**
     * Create a new customer record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomer(array $data): Customer
    {
        return $this->executeInTransaction(function () use ($data): Customer {
            /** @var Customer */
            return $this->customerRepository->create($data);
        });
    }

    /**
     * Update an existing customer record.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return $this->executeInTransaction(function () use ($customer, $data): Customer {
            $this->customerRepository->update($customer->id, $data);

            return $customer->refresh();
        });
    }

    /**
     * Delete a customer record.
     */
    public function deleteCustomer(Customer $customer): bool
    {
        return $this->executeInTransaction(function () use ($customer): bool {
            return $this->customerRepository->delete($customer->id);
        });
    }
}
