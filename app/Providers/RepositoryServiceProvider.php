<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Interfaces\EquipmentRepositoryInterface;
use App\Interfaces\GrandeurRepositoryInterface;
use App\Interfaces\SiteRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\WarrantyRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\EquipmentRepository;
use App\Repositories\GrandeurRepository;
use App\Repositories\SiteRepository;
use App\Repositories\UserRepository;
use App\Repositories\WarrantyRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(SiteRepositoryInterface::class, SiteRepository::class);
        $this->app->bind(WarrantyRepositoryInterface::class, WarrantyRepository::class);
        $this->app->bind(EquipmentRepositoryInterface::class, EquipmentRepository::class);
        $this->app->bind(GrandeurRepositoryInterface::class, GrandeurRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
