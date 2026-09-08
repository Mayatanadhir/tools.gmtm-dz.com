<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_is_automatically_logged(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('User has been created', $activity->description);
        $this->assertSame('John Doe', $activity->attribute_changes['attributes']['name']);
        $this->assertSame('john@example.com', $activity->attribute_changes['attributes']['email']);
        $this->assertArrayNotHasKey('password', $activity->attribute_changes['attributes'] ?? []);
    }

    public function test_user_update_logs_only_dirty_and_tracks_old_and_new_values(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $user->update([
            'name' => 'Updated Name',
        ]);

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('User has been updated', $activity->description);
        $this->assertSame('Original Name', $activity->attribute_changes['old']['name']);
        $this->assertSame('Updated Name', $activity->attribute_changes['attributes']['name']);
        $this->assertArrayNotHasKey('email', $activity->attribute_changes['attributes'] ?? []);
    }

    public function test_sensitive_attributes_like_password_are_never_logged(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $initialCount = Activity::count();

        // Updating only password should NOT submit an empty log
        $user->update([
            'password' => 'new-secret456',
        ]);

        $this->assertSame($initialCount, Activity::count());

        $latestActivity = Activity::latest('id')->first();
        if ($latestActivity && isset($latestActivity->attribute_changes['attributes'])) {
            $this->assertArrayNotHasKey('password', $latestActivity->attribute_changes['attributes']);
        }
    }

    public function test_authenticated_causer_is_tracked_in_activity_log(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'name' => 'Admin Actor',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Target User',
        ]);

        $this->actingAs($admin);

        $targetUser->update([
            'name' => 'Target Modified by Admin',
        ]);

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($admin->id, $activity->causer_id);
        $this->assertSame(User::class, $activity->causer_type);
        $this->assertTrue($activity->causer->is($admin));
    }

    public function test_user_can_retrieve_caused_activities(): void
    {
        /** @var User $actor */
        $actor = User::factory()->create();

        activity()
            ->causedBy($actor)
            ->log('Performed manual action');

        $this->assertCount(1, $actor->activitiesAsCauser);
        $this->assertSame('Performed manual action', $actor->activitiesAsCauser->first()->description);
    }
}
