<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_seeder_creates_expected_roles_and_permissions(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'Super-Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'User']);

        $this->assertDatabaseHas('permissions', ['name' => 'view instruments']);
        $this->assertDatabaseHas('permissions', ['name' => 'create instruments']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit instruments']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete instruments']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage users']);
    }

    public function test_user_can_be_assigned_role_and_inherits_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole('Admin');

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertTrue($user->can('create instruments'));
        $this->assertTrue($user->can('edit instruments'));
        $this->assertTrue($user->can('delete instruments'));
        $this->assertFalse($user->can('manage users'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole('Super-Admin');

        $this->assertTrue($user->hasRole('Super-Admin'));
        $this->assertTrue($user->can('manage users'));
        $this->assertTrue($user->can('delete instruments'));
        $this->assertTrue($user->can('create instruments'));
    }

    public function test_role_middleware_allows_authorized_user_and_blocks_unauthorized(): void
    {
        Route::get('/test-admin-route', fn () => response()->json(['status' => 'ok']))
            ->middleware(['web', 'role:Admin']);

        /** @var User $normalUser */
        $normalUser = User::factory()->create();
        $normalUser->assignRole('User');

        $this->actingAs($normalUser)
            ->get('/test-admin-route')
            ->assertForbidden();

        /** @var User $adminUser */
        $adminUser = User::factory()->create();
        $adminUser->assignRole('Admin');

        $this->actingAs($adminUser)
            ->get('/test-admin-route')
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    public function test_permission_middleware_blocks_unauthorized_user(): void
    {
        Route::get('/test-delete-route', fn () => response()->json(['status' => 'deleted']))
            ->middleware(['web', 'permission:delete instruments']);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user)
            ->get('/test-delete-route')
            ->assertForbidden();

        $user->givePermissionTo('delete instruments');

        $this->actingAs($user)
            ->get('/test-delete-route')
            ->assertOk()
            ->assertJson(['status' => 'deleted']);
    }
}
