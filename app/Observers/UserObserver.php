<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Notifications\SystemActivityAlert;
use App\Services\PermissionDiscoveryService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class UserObserver
{
    /**
     * Handle the User "saving" event.
     * Automatically computes or clears the photo_hash in the background based on profile_photo_path.
     */
    public function saving(User $user): void
    {
        if ($user->isDirty('profile_photo_path')) {
            if (blank($user->profile_photo_path)) {
                $user->photo_hash = null;
            } else {
                $disk = Storage::disk('public');
                if ($disk->exists($user->profile_photo_path)) {
                    $user->photo_hash = hash('sha256', (string) $disk->get($user->profile_photo_path));
                } else {
                    $user->photo_hash = hash('sha256', (string) $user->profile_photo_path);
                }
            }
        }
    }

    /**
     * Handle the User "created" event.
     * Assigns the default 'User' role and dispatches a SystemActivityAlert to all admins.
     */
    public function created(User $user): void
    {
        // Assign the default role to every new user unless they already have one.
        try {
            if ($user->roles()->count() === 0) {
                $defaultRole = app(PermissionDiscoveryService::class)->getDefaultRole();
                $user->assignRole($defaultRole);
            }
        } catch (RoleDoesNotExist) {
            // Roles have not been seeded yet — skip silently.
        }

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
