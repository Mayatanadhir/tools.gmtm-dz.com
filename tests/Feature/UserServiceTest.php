<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->app->make(UserService::class);
    }

    public function test_register_creates_user_with_hashed_password(): void
    {
        $user = $this->userService->register([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'plain-password-123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertTrue(Hash::check('plain-password-123', $user->password));
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_update_profile_updates_data_and_resets_verification_when_email_changes(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $updated = $this->userService->updateProfile($user, [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ]);

        $this->assertTrue($updated);
        $fresh = $user->fresh();
        $this->assertSame('Updated Name', $fresh->name);
        $this->assertSame('newemail@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
    }

    public function test_change_password_hashes_and_persists_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $result = $this->userService->changePassword($user, 'new-brand-password');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('new-brand-password', $user->fresh()->password));
    }

    public function test_delete_account_removes_user(): void
    {
        $user = User::factory()->create();

        $result = $this->userService->deleteAccount($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_get_user_by_id_and_email(): void
    {
        $user = User::factory()->create(['email' => 'findme@example.com']);

        $byId = $this->userService->getUserById($user->id);
        $byEmail = $this->userService->getUserByEmail('findme@example.com');

        $this->assertNotNull($byId);
        $this->assertNotNull($byEmail);
        $this->assertSame($user->id, $byId->id);
        $this->assertSame($user->id, $byEmail->id);
    }
}
