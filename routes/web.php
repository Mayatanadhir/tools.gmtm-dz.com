<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemTableController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

$routes = function (): void {
    Route::get('/', function () {
        try {
            if (! Schema::hasTable('users') || User::count() === 0) {
                return redirect()->route('system-tables.setup');
            }
        } catch (Throwable) {
            // Graceful fallback if database connection is not yet configured
        }

        return view('welcome');
    })->name('welcome');

    Route::prefix('system-tables')->name('system-tables.')->group(function () {
        Route::get('/setup', [SystemTableController::class, 'setup'])->name('setup');
        Route::post('/setup', [SystemTableController::class, 'storeSetup'])->name('setup.store');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware(['auth', 'verified', 'role:Super-Admin'])->prefix('system-tables')->name('system-tables.')->group(function () {
        Route::get('/', [SystemTableController::class, 'index'])->name('index');
        Route::get('/users', [SystemTableController::class, 'users'])->name('users');
        Route::post('/users', [SystemTableController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [SystemTableController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/toggle-status', [SystemTableController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [SystemTableController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/roles', [SystemTableController::class, 'roles'])->name('roles');
        Route::post('/roles', [SystemTableController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}', [SystemTableController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [SystemTableController::class, 'destroyRole'])->name('roles.destroy');
        Route::post('/permissions', [SystemTableController::class, 'storePermission'])->name('permissions.store');
        Route::put('/permissions/{permission}', [SystemTableController::class, 'updatePermission'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [SystemTableController::class, 'destroyPermission'])->name('permissions.destroy');
        Route::get('/activity-log', [SystemTableController::class, 'activityLog'])->name('activity-log');
        Route::get('/notifications', [SystemTableController::class, 'notifications'])->name('notifications');
        Route::get('/queues', [SystemTableController::class, 'queues'])->name('queues');
        Route::get('/cache', [SystemTableController::class, 'cache'])->name('cache');
        Route::get('/pruning', [SystemTableController::class, 'pruningSettings'])->name('pruning');
        Route::post('/pruning/settings', [SystemTableController::class, 'updatePruningSettings'])->name('pruning.update');
        Route::post('/pruning/dry-run', [SystemTableController::class, 'dryRunPruning'])->name('pruning.dry-run');
        Route::post('/pruning/execute', [SystemTableController::class, 'executePruning'])->name('pruning.execute');
        Route::post('/pruning/reset', [SystemTableController::class, 'resetPruningSettings'])->name('pruning.reset');
        Route::post('/pruning/tables', [SystemTableController::class, 'addPruningTable'])->name('pruning.tables.add');
        Route::delete('/pruning/tables/{table}', [SystemTableController::class, 'removePruningTable'])->name('pruning.tables.remove');
        Route::get('/backups', [SystemTableController::class, 'backups'])->name('backups');
        Route::post('/backups/create', [SystemTableController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/download/{file}', [SystemTableController::class, 'downloadBackup'])->name('backups.download');
        Route::post('/backups/restore', [SystemTableController::class, 'restoreBackup'])->name('backups.restore');
        Route::post('/backups/restore-oldest', [SystemTableController::class, 'restoreOldestBackup'])->name('backups.restore-oldest');
        Route::delete('/backups/{file}', [SystemTableController::class, 'deleteBackup'])->name('backups.delete');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
};

if (app()->runningUnitTests()) {
    foreach (['en', 'fr'] as $locale) {
        Route::group([
            'prefix' => $locale,
            'as' => "{$locale}.",
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
        ], $routes);
    }
}

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], $routes);
