<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\Metrology\CalibrationCertificateController;
use App\Http\Controllers\MetrologyController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemTableController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

$routes = function (): void {
    Route::get('lang/{locale}', function (string $locale) {
        if (in_array($locale, ['ar', 'en', 'fr'])) {
            session()->put('locale', $locale);

            return redirect(LaravelLocalization::getLocalizedURL($locale, null, [], true));
        }

        return redirect()->back();
    })->name('lang.switch');

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
        return view('workspace');
    })->middleware(['auth', 'verified'])->name('dashboard');

    // 🔬 Metrology & Equipments
    Route::middleware(['auth', 'verified'])->prefix('metrology')->name('metrology.')->group(function () {
        Route::get('/', [MetrologyController::class, 'index'])->name('index');
        Route::get('/instruments', [MetrologyController::class, 'instruments'])->name('instruments');
        Route::get('/equipment', [MetrologyController::class, 'equipment'])->name('equipment');
        Route::post('/equipment', [MetrologyController::class, 'storeEquipment'])->name('equipment.store');
        Route::get('/equipment/{equipment}', [MetrologyController::class, 'showEquipment'])->name('equipment.show');
        Route::put('/equipment/{equipment}', [MetrologyController::class, 'updateEquipment'])->name('equipment.update');
        Route::delete('/equipment/{equipment}', [MetrologyController::class, 'destroyEquipment'])->name('equipment.destroy');
        Route::get('/calibrator-movements', [MetrologyController::class, 'calibratorMovements'])->name('calibrator-movements');

        // Calibration Certificates Resource Suite
        Route::get('/calibration-certificates', [CalibrationCertificateController::class, 'index'])->name('calibration-certificates');
        Route::get('/calibration-certificates/create', [CalibrationCertificateController::class, 'create'])->name('calibration-certificates.create');
        Route::post('/calibration-certificates', [CalibrationCertificateController::class, 'store'])->name('calibration-certificates.store');
        Route::get('/calibration-certificates/{certificate}', [CalibrationCertificateController::class, 'show'])->name('calibration-certificates.show');
        Route::get('/calibration-certificates/{certificate}/edit', [CalibrationCertificateController::class, 'edit'])->name('calibration-certificates.edit');
        Route::put('/calibration-certificates/{certificate}', [CalibrationCertificateController::class, 'update'])->name('calibration-certificates.update');
        Route::delete('/calibration-certificates/{certificate}', [CalibrationCertificateController::class, 'destroy'])->name('calibration-certificates.destroy');
        Route::post('/calibration-certificates/{certificate}/approve', [CalibrationCertificateController::class, 'approve'])->name('calibration-certificates.approve');
        Route::post('/calibration-certificates/{certificate}/unlock', [CalibrationCertificateController::class, 'unlock'])->name('calibration-certificates.unlock');
        Route::get('/calibration-certificates/{certificate}/download', [CalibrationCertificateController::class, 'download'])->name('calibration-certificates.download');

        Route::get('/units', [MetrologyController::class, 'units'])->name('units');
        Route::post('/units', [MetrologyController::class, 'storeUnit'])->name('units.store');
        Route::put('/units/{grandeur}', [MetrologyController::class, 'updateUnit'])->name('units.update');
        Route::delete('/units/{grandeur}', [MetrologyController::class, 'destroyUnit'])->name('units.destroy');
    });
    Route::middleware(['auth', 'verified'])->get('/dashboard/metrology', [MetrologyController::class, 'index'])->name('dashboard_metrology');

    // 💼 Operations & Projects
    Route::middleware(['auth', 'verified'])->prefix('operations')->name('operations.')->group(function () {
        Route::get('/', [OperationsController::class, 'index'])->name('index');
        Route::get('/missions', [OperationsController::class, 'missions'])->name('missions');
        Route::get('/contracts', [OperationsController::class, 'contracts'])->name('contracts');
        Route::get('/attachments', [OperationsController::class, 'attachments'])->name('attachments');
        Route::get('/warranties', [OperationsController::class, 'warranties'])->name('warranties');
        Route::post('/warranties', [OperationsController::class, 'storeWarranty'])->name('warranties.store');
        Route::put('/warranties/{warranty}', [OperationsController::class, 'updateWarranty'])->name('warranties.update');
        Route::delete('/warranties/{warranty}', [OperationsController::class, 'destroyWarranty'])->name('warranties.destroy');
        Route::get('/article-types', [OperationsController::class, 'articleTypes'])->name('article-types');
    });
    Route::middleware(['auth', 'verified'])->get('/dashboard/operations', [OperationsController::class, 'index'])->name('dashboard_operations');

    // 📈 Internal and Analytical Management
    Route::middleware(['auth', 'verified'])->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/expenses', [AnalyticsController::class, 'expenses'])->name('expenses');
        Route::get('/forecasts', [AnalyticsController::class, 'forecasts'])->name('forecasts');
        Route::get('/statistics', [AnalyticsController::class, 'statistics'])->name('statistics');
        Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
    });
    Route::middleware(['auth', 'verified'])->get('/dashboard/analytics', [AnalyticsController::class, 'index'])->name('dashboard_analytics');

    // 🗂️ Master Data / Reference Data
    Route::middleware(['auth', 'verified'])->prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');
        Route::get('/clients', [MasterDataController::class, 'clients'])->name('clients');
        Route::post('/clients', [MasterDataController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{customer}', [MasterDataController::class, 'updateClient'])->name('clients.update');
        Route::delete('/clients/{customer}', [MasterDataController::class, 'destroyClient'])->name('clients.destroy');
        Route::get('/employees', [MasterDataController::class, 'employees'])->name('employees');
        Route::post('/employees', [MasterDataController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}', [MasterDataController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [MasterDataController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::get('/sites', [MasterDataController::class, 'sites'])->name('sites');
        Route::post('/sites', [MasterDataController::class, 'storeSite'])->name('sites.store');
        Route::put('/sites/{site}', [MasterDataController::class, 'updateSite'])->name('sites.update');
        Route::delete('/sites/{site}', [MasterDataController::class, 'destroySite'])->name('sites.destroy');
    });
    Route::middleware(['auth', 'verified'])->get('/dashboard/master-data', [MasterDataController::class, 'index'])->name('dashboard_master_data');

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
        Route::get('/settings', [SystemTableController::class, 'settings'])->name('settings');
        Route::post('/settings/toggle-registration', [SystemTableController::class, 'toggleRegistration'])->name('settings.toggle-registration');
        Route::post('/settings/update', [SystemTableController::class, 'updateSetting'])->name('settings.update');
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
