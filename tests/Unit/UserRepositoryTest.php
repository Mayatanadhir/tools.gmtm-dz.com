<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_it_resolves_user_repository_instance(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->repository);
    }

    public function test_can_create_user(): void
    {
        $user = $this->repository->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_can_find_user_by_id(): void
    {
        $created = User::factory()->create();

        $found = $this->repository->find($created->id);

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
    }

    public function test_can_find_user_by_email(): void
    {
        $created = User::factory()->create(['email' => 'specific@example.com']);

        $found = $this->repository->findByEmail('specific@example.com');

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $result = $this->repository->update($user->id, ['name' => 'New Name']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_update_password(): void
    {
        $user = User::factory()->create();
        $newHashedPassword = Hash::make('newpassword');

        $result = $this->repository->updatePassword($user, $newHashedPassword);

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_can_get_verified_users(): void
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $verifiedUsers = $this->repository->getVerifiedUsers(10);

        $this->assertSame(1, $verifiedUsers->total());
    }
}
