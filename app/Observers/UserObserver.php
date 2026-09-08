<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Notifications\SystemActivityAlert;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Dispatches a SystemActivityAlert to all Super-Admin and Admin users.
     */
    public function created(User $user): void
    {
        try {
            $causer = auth()->user()?->name ?? 'النظام';
            $admins = User::role(['Super-Admin', 'Admin'])->get();

            Notification::send($admins, new SystemActivityAlert(
                title: 'مستخدم جديد',
                message: "تمت إضافة مستخدم جديد باسم: {$user->name}",
                type: 'created',
                causer: $causer,
                extra: [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            ));
        } catch (RoleDoesNotExist) {
            // Roles have not been seeded yet — skip notification dispatch silently.
        }
    }

    /**
     * Handle the User "deleted" event.
     * Dispatches a SystemActivityAlert to all Super-Admin and Admin users.
     */
    public function deleted(User $user): void
    {
        try {
            $causer = auth()->user()?->name ?? 'النظام';
            $admins = User::role(['Super-Admin', 'Admin'])->get();

            Notification::send($admins, new SystemActivityAlert(
                title: 'حذف مستخدم',
                message: "تم حذف المستخدم: {$user->name}",
                type: 'deleted',
                causer: $causer,
                extra: [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            ));
        } catch (RoleDoesNotExist) {
            // Roles have not been seeded yet — skip notification dispatch silently.
        }
    }
}
