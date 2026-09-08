<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_login_when_accessing_system_tables(): void
    {
        $response = $this->get(route('system-tables.index'));
        $response->assertRedirect(route('login'));

        $responseUsers = $this->get(route('system-tables.users'));
        $responseUsers->assertRedirect(route('login'));

        $responseRoles = $this->get(route('system-tables.roles'));
        $responseRoles->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_system_tables_overview(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.index'));

        $response->assertOk();
        $response->assertViewIs('system.index');
        $response->assertViewHasAll(['stats', 'recentActivities']);
        $response->assertSee(__('System & Database Explorer'));
    }

    public function test_authenticated_user_can_access_users_and_sessions_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'John Engineer']);

        $response = $this->actingAs($user)->get(route('system-tables.users'));

        $response->assertOk();
        $response->assertViewIs('system.users');
        $response->assertViewHasAll(['users', 'sessions']);
        $response->assertSee('John Engineer');

        // Test search
        $searchResponse = $this->actingAs($user)->get(route('system-tables.users', ['search' => 'Engineer']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('John Engineer');
    }

    public function test_authenticated_user_can_access_roles_and_permissions_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.roles'));

        $response->assertOk();
        $response->assertViewIs('system.roles');
        $response->assertViewHasAll(['roles', 'permissions']);
        $response->assertSee('Super-Admin');
        $response->assertSee('Admin');
    }

    public function test_authenticated_user_can_access_activity_log_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Activity Actor']);

        $response = $this->actingAs($user)->get(route('system-tables.activity-log'));

        $response->assertOk();
        $response->assertViewIs('system.activity-log');
        $response->assertViewHas('activities');
    }

    public function test_authenticated_user_can_access_notifications_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.notifications'));

        $response->assertOk();
        $response->assertViewIs('system.notifications');
        $response->assertViewHas('notifications');
    }

    public function test_authenticated_user_can_access_queues_explorer_with_tabs(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Jobs tab
        $responseJobs = $this->actingAs($user)->get(route('system-tables.queues', ['tab' => 'jobs']));
        $responseJobs->assertOk();
        $responseJobs->assertViewIs('system.queues');
        $responseJobs->assertViewHasAll(['jobs', 'failedJobs', 'batches']);

        // Failed tab
        $responseFailed = $this->actingAs($user)->get(route('system-tables.queues', ['tab' => 'failed']));
        $responseFailed->assertOk();

        // Batches tab
        $responseBatches = $this->actingAs($user)->get(route('system-tables.queues', ['tab' => 'batches']));
        $responseBatches->assertOk();
    }

    public function test_authenticated_user_can_access_cache_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.cache'));

        $response->assertOk();
        $response->assertViewIs('system.cache');
        $response->assertViewHasAll(['cacheEntries', 'cacheLocks']);
    }

    public function test_guests_cannot_create_users_via_system_interface(): void
    {
        $response = $this->post(route('system-tables.users.store'), [
            'name' => 'Guest New User',
            'email' => 'guest@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_create_new_user_with_role_and_audit(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.users.store'), [
            'name' => 'System Created User',
            'email' => 'system_user@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => 'Admin',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'name' => 'System Created User',
            'email' => 'system_user@example.com',
        ]);

        $createdUser = User::where('email', 'system_user@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Admin'));
    }

    public function test_create_user_validates_required_fields_and_unique_email(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $existing = User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($admin)->post(route('system-tables.users.store'), [
            'name' => '',
            'email' => 'duplicate@example.com',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_guests_cannot_update_users_via_system_interface(): void
    {
        /** @var User $target */
        $target = User::factory()->create();

        $response = $this->put(route('system-tables.users.update', $target), [
            'name' => 'Attempted Edit',
            'email' => 'edit@example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_update_user_name_email_and_role(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        /** @var User $target */
        $target = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $target), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'Admin',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');

        $target->refresh();
        $this->assertSame('Updated Name', $target->name);
        $this->assertSame('updated@example.com', $target->email);
        $this->assertTrue($target->hasRole('Admin'));
    }

    public function test_update_user_validates_email_uniqueness_except_for_own_email(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        /** @var User $user1 */
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        /** @var User $user2 */
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Keeping own email should succeed
        $responseSame = $this->actingAs($admin)->put(route('system-tables.users.update', $user1), [
            'name' => 'User One Updated',
            'email' => 'user1@example.com',
        ]);
        $responseSame->assertSessionHasNoErrors();

        // Taking another user's email should fail
        $responseDuplicate = $this->actingAs($admin)->put(route('system-tables.users.update', $user1), [
            'name' => 'User One Stolen Email',
            'email' => 'user2@example.com',
        ]);
        $responseDuplicate->assertSessionHasErrors(['email']);
    }

    public function test_guests_cannot_create_roles_or_permissions(): void
    {
        $responseRole = $this->post(route('system-tables.roles.store'), [
            'name' => 'TestRole',
        ]);
        $responseRole->assertRedirect(route('login'));

        $responsePermission = $this->post(route('system-tables.permissions.store'), [
            'name' => 'test permission',
        ]);
        $responsePermission->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_create_new_role_with_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.roles.store'), [
            'name' => 'Supervisor',
            'permissions' => ['view instruments', 'create instruments'],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $role = Role::findByName('Supervisor', 'web');
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view instruments'));
        $this->assertTrue($role->hasPermissionTo('create instruments'));
        $this->assertFalse($role->hasPermissionTo('delete instruments'));
    }

    public function test_authenticated_admin_can_create_new_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.permissions.store'), [
            'name' => 'export data',
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $permission = Permission::findByName('export data', 'web');
        $this->assertNotNull($permission);
        $this->assertSame('web', $permission->guard_name);
    }

    public function test_create_role_and_permission_validates_unique_names(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        // Role 'Admin' already seeded
        $responseDuplicateRole = $this->actingAs($admin)->post(route('system-tables.roles.store'), [
            'name' => 'Admin',
        ]);
        $responseDuplicateRole->assertSessionHasErrors(['name']);

        // Permission 'view instruments' already seeded
        $responseDuplicatePermission = $this->actingAs($admin)->post(route('system-tables.permissions.store'), [
            'name' => 'view instruments',
        ]);
        $responseDuplicatePermission->assertSessionHasErrors(['name']);
    }

    public function test_guests_cannot_update_roles_or_permissions(): void
    {
        $role = Role::findByName('Admin', 'web');
        $permission = Permission::findByName('manage users', 'web');

        $responseRole = $this->put(route('system-tables.roles.update', $role), [
            'name' => 'Hacked Role',
        ]);
        $responseRole->assertRedirect(route('login'));

        $responsePermission = $this->put(route('system-tables.permissions.update', $permission), [
            'name' => 'hacked permission',
        ]);
        $responsePermission->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_update_role_and_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $role = Role::create(['name' => 'Coordinator', 'guard_name' => 'web']);
        $role->syncPermissions(['view instruments']);

        $response = $this->actingAs($admin)->put(route('system-tables.roles.update', $role), [
            'name' => 'Senior Coordinator',
            'permissions' => ['view instruments', 'manage users'],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $role->refresh();
        $this->assertSame('Senior Coordinator', $role->name);
        $this->assertTrue($role->hasPermissionTo('view instruments'));
        $this->assertTrue($role->hasPermissionTo('manage users'));
    }

    public function test_authenticated_admin_can_update_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $permission = Permission::create(['name' => 'temp permission', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->put(route('system-tables.permissions.update', $permission), [
            'name' => 'permanent permission',
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $permission->refresh();
        $this->assertSame('permanent permission', $permission->name);
    }

    public function test_update_role_and_permission_validates_unique_names_ignoring_self(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $role = Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'staff action', 'guard_name' => 'web']);

        // Keeping own role name succeeds
        $responseSameRole = $this->actingAs($admin)->put(route('system-tables.roles.update', $role), [
            'name' => 'Staff',
        ]);
        $responseSameRole->assertSessionHasNoErrors();

        // Taking existing role name fails
        $responseDuplicateRole = $this->actingAs($admin)->put(route('system-tables.roles.update', $role), [
            'name' => 'Admin',
        ]);
        $responseDuplicateRole->assertSessionHasErrors(['name']);

        // Keeping own permission name succeeds
        $responseSamePermission = $this->actingAs($admin)->put(route('system-tables.permissions.update', $permission), [
            'name' => 'staff action',
        ]);
        $responseSamePermission->assertSessionHasNoErrors();

        // Taking existing permission name fails
        $responseDuplicatePermission = $this->actingAs($admin)->put(route('system-tables.permissions.update', $permission), [
            'name' => 'manage users',
        ]);
        $responseDuplicatePermission->assertSessionHasErrors(['name']);
    }

    public function test_authenticated_user_can_delete_a_role(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->delete(route('system-tables.roles.destroy', $role));

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_authenticated_user_can_delete_a_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $permission = Permission::create(['name' => 'temporary-action', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->delete(route('system-tables.permissions.destroy', $permission));

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_guest_cannot_delete_a_role(): void
    {
        $role = Role::create(['name' => 'Guest Target Role', 'guard_name' => 'web']);

        $response = $this->delete(route('system-tables.roles.destroy', $role));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_guest_cannot_delete_a_permission(): void
    {
        $permission = Permission::create(['name' => 'guest-target-action', 'guard_name' => 'web']);

        $response = $this->delete(route('system-tables.permissions.destroy', $permission));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }
}
