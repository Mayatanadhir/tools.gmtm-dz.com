<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * الأدوار الخارقة التي تتجاوز كل الصلاحيات
     *
     * @var array<int, string>
     */
    protected array $superRoles = ['Super-Admin'];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            return (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($this->superRoles)) ? true : null;
        });
    }
}
