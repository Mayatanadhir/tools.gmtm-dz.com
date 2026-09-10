<?php

use App\Http\Middleware\EnsureDatabaseIsMigrated;
use App\Http\Middleware\EnsureRegistrationIsOpen;
use App\Http\Middleware\EnsureSuperAdminExists;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureDatabaseIsMigrated::class,
            EnsureSuperAdminExists::class,
            SetLocale::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'localize' => LaravelLocalizationRoutes::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeCookieRedirect' => LocaleCookieRedirect::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
            'setLocale' => SetLocale::class,
            'registration.open' => EnsureRegistrationIsOpen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PDOException $e, Request $request) {
            if (! app()->runningUnitTests() && ! $request->is('api/*') && ! $request->expectsJson()) {
                $defaultConn = (string) config('database.default');

                return response()->view('errors.database', [
                    'connection' => $defaultConn,
                    'database' => (string) config("database.connections.{$defaultConn}.database"),
                    'host' => (string) config("database.connections.{$defaultConn}.host", '127.0.0.1'),
                    'port' => (string) config("database.connections.{$defaultConn}.port", '3306'),
                ], 503);
            }
        });
    })->create();
