<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemActivityAlert;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DatabaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    // -----------------------------------------------------------------------
    // SystemActivityAlert: Payload & Channel
    // -----------------------------------------------------------------------

    public function test_system_activity_alert_uses_database_channel_only(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        Notification::fake();

        $admin->notify(new SystemActivityAlert(
            title: 'Test Title',
            message: 'Test message body',
            type: 'created',
            causer: 'Test Actor',
            extra: ['key' => 'value']
        ));

        Notification::assertSentTo($admin, SystemActivityAlert::class, function (SystemActivityAlert $notification) use ($admin) {
            $this->assertSame(['database'], $notification->via($admin));

            return true;
        });
    }

    public function test_system_activity_alert_payload_structure_is_correct(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $admin->notify(new SystemActivityAlert(
            title: 'مستخدم جديد',
            message: 'تمت إضافة مستخدم جديد',
            type: 'created',
            causer: 'المدير',
            extra: ['user_id' => 99, 'email' => 'test@test.com']
        ));

        $notification = $admin->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('مستخدم جديد', $notification->data['title']);
        $this->assertSame('تمت إضافة مستخدم جديد', $notification->data['message']);
        $this->assertSame('created', $notification->data['type']);
        $this->assertSame('المدير', $notification->data['causer']);
        $this->assertSame(['user_id' => 99, 'email' => 'test@test.com'], $notification->data['extra']);
    }

    public function test_system_activity_alert_uses_default_causer_when_not_provided(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $admin->notify(new SystemActivityAlert(
            title: 'Event',
            message: 'System event occurred',
            type: 'deleted'
        ));

        $notification = $admin->notifications()->latest()->first();
        $this->assertSame('النظام', $notification->data['causer']);
    }

    // -----------------------------------------------------------------------
    // UserObserver: created event
    // -----------------------------------------------------------------------

    public function test_observer_dispatches_notification_to_admins_when_user_is_created(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create(['name' => 'Super Admin']);
        $superAdmin->assignRole('Super-Admin');

        /** @var User $admin */
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('Admin');

        /** @var User $actor */
        $actor = User::factory()->create();
        $this->actingAs($actor);

        // Trigger the observer by creating a new user
        $newUser = User::factory()->create(['name' => 'New Member', 'email' => 'new@test.com']);

        // Find the notification specifically for the new user's creation
        $superAdminNotification = $superAdmin->notifications()
            ->where('type', SystemActivityAlert::class)
            ->whereJsonContains('data->extra->user_id', $newUser->id)
            ->first();

        $adminNotification = $admin->notifications()
            ->where('type', SystemActivityAlert::class)
            ->whereJsonContains('data->extra->user_id', $newUser->id)
            ->first();

        $this->assertNotNull($superAdminNotification);
        $this->assertNotNull($adminNotification);

        $this->assertSame('مستخدم جديد', $superAdminNotification->data['title']);
        $this->assertSame('created', $superAdminNotification->data['type']);
        $this->assertStringContainsString('New Member', $superAdminNotification->data['message']);
        $this->assertSame($newUser->id, $superAdminNotification->data['extra']['user_id']);
    }

    public function test_observer_dispatches_notification_to_admins_when_user_is_deleted(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        /** @var User $targetUser */
        $targetUser = User::factory()->create(['name' => 'Doomed User', 'email' => 'doomed@test.com']);

        $this->actingAs($admin);

        $targetUserId = $targetUser->id;
        $targetUser->delete();

        // Filter specifically for the deleted event to avoid picking up the 'created' notification
        $notification = $admin->notifications()
            ->whereJsonContains('data->type', 'deleted')
            ->whereJsonContains('data->extra->user_id', $targetUserId)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('حذف مستخدم', $notification->data['title']);
        $this->assertSame('deleted', $notification->data['type']);
        $this->assertSame($targetUserId, $notification->data['extra']['user_id']);
    }

    public function test_regular_user_role_does_not_receive_admin_notifications(): void
    {
        /** @var User $regularUser */
        $regularUser = User::factory()->create();
        $regularUser->assignRole('User');

        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Create a new user to trigger the observer
        User::factory()->create();

        // Regular user should have NO notifications
        $this->assertSame(0, $regularUser->notifications()->count());
        // Admin should have received it
        $this->assertGreaterThan(0, $admin->notifications()->count());
    }

    // -----------------------------------------------------------------------
    // NotificationController API Endpoints
    // -----------------------------------------------------------------------

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->getJson('/api/notifications/unread')->assertUnauthorized();
    }

    public function test_index_returns_paginated_notifications_for_authenticated_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->notify(new SystemActivityAlert('Title 1', 'Message 1', 'created'));
        $user->notify(new SystemActivityAlert('Title 2', 'Message 2', 'deleted'));

        $response = $this->actingAs($user)
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_unread_returns_only_unread_notifications_with_count(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->notify(new SystemActivityAlert('Unread 1', 'Msg', 'created'));
        $user->notify(new SystemActivityAlert('Unread 2', 'Msg', 'created'));

        // Mark one as read
        $user->notifications()->first()->markAsRead();

        $response = $this->actingAs($user)
            ->getJson('/api/notifications/unread');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_mark_as_read_updates_specific_notification(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->notify(new SystemActivityAlert('To Read', 'Msg', 'created'));

        $notification = $user->unreadNotifications()->first();
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNotNull($user->notifications()->find($notification->id)->read_at);
    }

    public function test_mark_as_read_cannot_affect_another_users_notification(): void
    {
        /** @var User $userA */
        $userA = User::factory()->create();
        /** @var User $userB */
        $userB = User::factory()->create();

        $userB->notify(new SystemActivityAlert('User B notification', 'Msg', 'created'));
        $userBNotification = $userB->notifications()->first();

        // User A attempts to mark User B's notification as read — must be rejected with 404
        $response = $this->actingAs($userA)
            ->patchJson("/api/notifications/{$userBNotification->id}/read");

        $response->assertNotFound();
        $this->assertNull($userB->fresh()->notifications()->find($userBNotification->id)?->read_at);
    }

    public function test_mark_all_as_read_marks_all_unread_notifications(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->notify(new SystemActivityAlert('First', 'Msg', 'created'));
        $user->notify(new SystemActivityAlert('Second', 'Msg', 'deleted'));

        $response = $this->actingAs($user)
            ->patchJson('/api/notifications/read-all');

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_destroy_deletes_notification_belonging_to_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->notify(new SystemActivityAlert('To Delete', 'Msg', 'created'));

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNull(DatabaseNotification::find($notification->id));
    }

    public function test_destroy_cannot_delete_another_users_notification(): void
    {
        /** @var User $userA */
        $userA = User::factory()->create();
        /** @var User $userB */
        $userB = User::factory()->create();

        $userB->notify(new SystemActivityAlert('User B notification', 'Msg', 'created'));
        $userBNotification = $userB->notifications()->first();

        $response = $this->actingAs($userA)
            ->deleteJson("/api/notifications/{$userBNotification->id}");

        // Must return 404 — User A cannot see User B's notifications
        $response->assertNotFound();
        $this->assertNotNull(DatabaseNotification::find($userBNotification->id));
    }
}
