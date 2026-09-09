<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
<<<<<<< HEAD
use Illuminate\Support\Facades\Schema;
=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
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
<<<<<<< HEAD

        $this->ensureSafeDriversWhenUnmigrated();
    }

    /**
     * Fallback to file-based drivers if database tables for session or cache are not yet migrated.
     */
    protected function ensureSafeDriversWhenUnmigrated(): void
    {
        try {
            if (config('session.driver') === 'database' && ! Schema::hasTable('sessions')) {
                config(['session.driver' => 'file']);
            }

            if (config('cache.default') === 'database' && ! Schema::hasTable('cache')) {
                config(['cache.default' => 'file']);
            }
        } catch (\Throwable) {
            config(['session.driver' => 'file']);
            config(['cache.default' => 'file']);
        }
=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
    }
}
