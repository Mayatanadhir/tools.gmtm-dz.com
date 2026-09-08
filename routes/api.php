<?php

declare(strict_types=1);

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes that require authentication via session or token.
|
*/

Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('/unread', [NotificationController::class, 'unread'])->name('api.notifications.unread');
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
});
