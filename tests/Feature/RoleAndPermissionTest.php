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

        $this->assertDatabaseHas('permissions', ['name' => 'view users']);
        $this->assertDatabaseHas('permissions', ['name' => 'create users']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit users']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete users']);
    }

    public function test_user_can_be_assigned_role_and_inherits_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole('Admin');

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertTrue($user->can('create users'));
        $this->assertTrue($user->can('edit users'));
        $this->assertTrue($user->can('view users'));
        $this->assertFalse($user->can('delete users'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole('Super-Admin');

        $this->assertTrue($user->hasRole('Super-Admin'));
        $this->assertTrue($user->can('delete users'));
        $this->assertTrue($user->can('create users'));
        $this->assertTrue($user->can('edit users'));
        $this->assertTrue($user->can('view users'));
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
            ->middleware(['web', 'permission:delete users']);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user)
            ->get('/test-delete-route')
            ->assertForbidden();

        $user->givePermissionTo('delete users');

        $this->actingAs($user)
            ->get('/test-delete-route')
            ->assertOk()
            ->assertJson(['status' => 'deleted']);
    }

    public function test_super_admin_bypasses_all_permission_checks_via_gate_before(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super-Admin');

        // Super-Admin can perform arbitrary abilities not even defined in database
        $this->assertTrue($superAdmin->can('arbitrary_undefined_super_ability'));
        $this->assertTrue($superAdmin->can('manage anything'));
    }

    public function test_regular_user_cannot_bypass_permissions_via_gate(): void
    {
        /** @var User $regularUser */
        $regularUser = User::factory()->create();
        $regularUser->assignRole('User');

        $this->assertFalse($regularUser->can('arbitrary_undefined_super_ability'));
    }

    public function test_is_super_admin_helper_method_on_user_model(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super-Admin');

        /** @var User $regularUser */
        $regularUser = User::factory()->create();
        $regularUser->assignRole('User');

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($regularUser->isSuperAdmin());
    }
<<<<<<< HEAD

    public function test_roles_page_renders_unified_table_with_translated_functional_columns(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super-Admin');

        $response = $this->actingAs($superAdmin)
            ->get(route('system-tables.roles'));

        $response->assertOk();
        $response->assertSeeText(__('Role & Function'));
        $response->assertSeeText(__('Functional Scope'));
        $response->assertSeeText(__('Assigned Users'));
        $response->assertSeeText(__('Privileges Scope'));
        $response->assertSeeText(__('Role Status'));
        $response->assertSeeText(__('Super Administrator (Full Sovereign Access)'));
    }
=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
}
