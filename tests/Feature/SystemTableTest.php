<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemActivityAlert;
use App\Services\SystemTableService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_regular_authenticated_users_without_super_admin_role_are_forbidden(): void
    {
        /** @var User $regularUser */
        $regularUser = User::factory()->create();
        $regularUser->assignRole('User');

        $this->actingAs($regularUser)->get(route('system-tables.index'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.users'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.roles'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.backups'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.pruning'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.queues'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.activity-log'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.notifications'))->assertForbidden();
        $this->actingAs($regularUser)->get(route('system-tables.cache'))->assertForbidden();
    }

    public function test_system_tables_navigation_link_is_hidden_for_regular_users(): void
    {
        /** @var User $regularUser */
        $regularUser = User::factory()->create();
        $regularUser->assignRole('User');

        $response = $this->actingAs($regularUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee(route('system-tables.index'));

        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $responseAdmin = $this->actingAs($superAdmin)->get(route('dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee(route('system-tables.index'));
    }

    public function test_authenticated_user_can_access_system_tables_overview(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get(route('system-tables.index'));

        $response->assertOk();
        $response->assertViewIs('system.index');
        $response->assertViewHasAll(['stats', 'recentActivities']);
        $response->assertSee(__('System & Database Explorer'));
    }

    public function test_authenticated_user_can_access_users_and_sessions_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create(['name' => 'John Engineer']);

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
        $user = User::factory()->superAdmin()->create();

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
        $user = User::factory()->superAdmin()->create(['name' => 'Activity Actor']);

        $response = $this->actingAs($user)->get(route('system-tables.activity-log'));

        $response->assertOk();
        $response->assertViewIs('system.activity-log');
        $response->assertViewHas('activities');
    }

    public function test_authenticated_user_can_access_notifications_explorer(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();
        $user->notify(new SystemActivityAlert('New Login', 'Logged in successfully', 'info'));

        $response = $this->actingAs($user)->get(route('system-tables.notifications'));

        $response->assertOk();
        $response->assertViewIs('system.notifications');
        $response->assertViewHas('notifications');
        $response->assertSeeText(__('System Activity Alert'));
        $response->assertSeeText('New Login');
    }

    public function test_authenticated_user_can_access_queues_explorer_with_tabs(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

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
        $user = User::factory()->superAdmin()->create();

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
        $admin = User::factory()->superAdmin()->create();

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
        $admin = User::factory()->superAdmin()->create();
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
        $admin = User::factory()->superAdmin()->create();
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

    public function test_updating_user_role_rejects_empty_or_null_role(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        /** @var User $target */
        $target = User::factory()->create([
            'name' => 'Role Test User',
            'email' => 'roletest@example.com',
        ]);
        $target->assignRole('Admin');

        // Test with empty string
        $responseEmpty = $this->actingAs($admin)->put(route('system-tables.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => '',
        ]);
        $responseEmpty->assertSessionHasErrors(['role']);

        // Test with null
        $responseNull = $this->actingAs($admin)->put(route('system-tables.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => null,
        ]);
        $responseNull->assertSessionHasErrors(['role']);

        // Role remains untouched
        $target->refresh();
        $this->assertTrue($target->hasRole('Admin'));
    }

    public function test_update_user_validates_email_uniqueness_except_for_own_email(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
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
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.roles.store'), [
            'name' => 'Supervisor',
            'permissions' => ['view users', 'create users'],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $role = Role::findByName('Supervisor', 'web');
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view users'));
        $this->assertTrue($role->hasPermissionTo('create users'));
        $this->assertFalse($role->hasPermissionTo('delete users'));
    }

    public function test_authenticated_admin_can_create_new_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

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
        $admin = User::factory()->superAdmin()->create();

        // Role 'Admin' already seeded
        $responseDuplicateRole = $this->actingAs($admin)->post(route('system-tables.roles.store'), [
            'name' => 'Admin',
        ]);
        $responseDuplicateRole->assertSessionHasErrors(['name']);

        // Permission 'view users' already seeded
        $responseDuplicatePermission = $this->actingAs($admin)->post(route('system-tables.permissions.store'), [
            'name' => 'view users',
        ]);
        $responseDuplicatePermission->assertSessionHasErrors(['name']);
    }

    public function test_guests_cannot_update_roles_or_permissions(): void
    {
        $role = Role::findByName('Admin', 'web');
        $permission = Permission::findByName('view users', 'web');

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
        $admin = User::factory()->superAdmin()->create();
        $role = Role::create(['name' => 'Coordinator', 'guard_name' => 'web']);
        $role->syncPermissions(['view users']);

        $response = $this->actingAs($admin)->put(route('system-tables.roles.update', $role), [
            'name' => 'Senior Coordinator',
            'permissions' => ['view users', 'create users'],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');

        $role->refresh();
        $this->assertSame('Senior Coordinator', $role->name);
        $this->assertTrue($role->hasPermissionTo('view users'));
        $this->assertTrue($role->hasPermissionTo('create users'));
    }

    public function test_authenticated_admin_can_update_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
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
        $admin = User::factory()->superAdmin()->create();
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
            'name' => 'view users',
        ]);
        $responseDuplicatePermission->assertSessionHasErrors(['name']);
    }

    public function test_authenticated_user_can_delete_a_role(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->delete(route('system-tables.roles.destroy', $role));

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_super_admin_role_cannot_be_deleted_due_to_anti_lockout(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        $superAdminRole = Role::findByName('Super-Admin', 'web');

        $response = $this->actingAs($admin)->delete(route('system-tables.roles.destroy', $superAdminRole));

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id, 'name' => 'Super-Admin']);
    }

    public function test_super_admin_role_cannot_be_modified_due_to_anti_lockout(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        $superAdminRole = Role::findByName('Super-Admin', 'web');

        $response = $this->actingAs($admin)->put(route('system-tables.roles.update', $superAdminRole), [
            'name' => 'Renamed Super-Admin',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id, 'name' => 'Super-Admin']);
    }

    public function test_authenticated_user_can_delete_a_permission(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
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

    public function test_activity_logs_explorer_displays_translated_descriptions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create(['name' => 'Admin User']);

        activity('system_users')
            ->performedOn($admin)
            ->log("Created new system user 'Special User'");

        // In Arabic (default)
        app()->setLocale('ar');
        $response = $this->actingAs($admin)->get(route('system-tables.activity-log'));
        $response->assertOk();
        $response->assertSee("إنشاء مستخدم نظام جديد 'Special User'");

        // In French
        app()->setLocale('fr');
        $responseFr = $this->actingAs($admin)->get(route('fr.system-tables.activity-log'));
        $responseFr->assertOk();
        $responseFr->assertSee("Nouvel utilisateur système 'Special User' créé");
    }

    public function test_system_table_service_translates_activity_descriptions_accurately_across_locales(): void
    {
        $service = app(SystemTableService::class);

        app()->setLocale('ar');
        $this->assertSame('تم إنشاء المستخدم', $service->translateActivityDescription('User has been created'));
        $this->assertSame("إنشاء دور نظام جديد 'Supervisor'", $service->translateActivityDescription("Created new system role 'Supervisor'"));
        $this->assertSame("حذف صلاحية النظام 'edit-users'", $service->translateActivityDescription("Deleted system permission 'edit-users'"));
        $this->assertSame("استعادة حالة قاعدة البيانات من اللقطة 'backup.zip'", $service->translateActivityDescription("Restored database state from snapshot 'backup.zip'"));

        app()->setLocale('fr');
        $this->assertSame("L'utilisateur a été créé", $service->translateActivityDescription('User has been created'));
        $this->assertSame("Nouveau rôle système 'Supervisor' créé", $service->translateActivityDescription("Created new system role 'Supervisor'"));
        $this->assertSame("Permission système 'edit-users' supprimée", $service->translateActivityDescription("Deleted system permission 'edit-users'"));
        $this->assertSame("État de la base de données restauré à partir de l'instantané 'backup.zip'", $service->translateActivityDescription("Restored database state from snapshot 'backup.zip'"));

        app()->setLocale('en');
        $this->assertSame('User has been created', $service->translateActivityDescription('User has been created'));
        $this->assertSame("Created new system role 'Supervisor'", $service->translateActivityDescription("Created new system role 'Supervisor'"));
    }

    public function test_authenticated_admin_can_create_user_with_status_and_photo_fields(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.users.store'), [
            'name' => 'Alice Engineer',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 'suspended',
            'profile_photo_path' => 'avatars/alice.jpg',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'status' => 'suspended',
            'profile_photo_path' => 'avatars/alice.jpg',
            'photo_hash' => hash('sha256', 'avatars/alice.jpg'),
        ]);
    }

    public function test_authenticated_admin_can_update_user_status_and_photo_fields(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        /** @var User $targetUser */
        $targetUser = User::factory()->create(['name' => 'Bob Dev', 'status' => 'active']);

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $targetUser), [
            'name' => 'Bob Senior Dev',
            'email' => $targetUser->email,
            'status' => 'suspended',
            'profile_photo_path' => 'avatars/bob-updated.png',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Bob Senior Dev',
            'status' => 'suspended',
            'profile_photo_path' => 'avatars/bob-updated.png',
            'photo_hash' => hash('sha256', 'avatars/bob-updated.png'),
        ]);
    }

    public function test_clearing_profile_photo_path_clears_photo_hash(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'profile_photo_path' => 'avatars/bob.png',
            'photo_hash' => hash('sha256', 'avatars/bob.png'),
        ]);

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'profile_photo_path' => '',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'profile_photo_path' => null,
            'photo_hash' => null,
        ]);
    }

    public function test_authenticated_admin_can_upload_photo_file_for_user(): void
    {
        Storage::fake('public');
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

        $file = UploadedFile::fake()->image('profile.jpg', 200, 200);

        $response = $this->actingAs($admin)->post(route('system-tables.users.store'), [
            'name' => 'Carol Upload',
            'email' => 'carol@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 'active',
            'photo' => $file,
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');

        $user = User::where('email', 'carol@example.com')->firstOrFail();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
        $this->assertNotNull($user->photo_hash);
    }

    public function test_authenticated_admin_can_remove_photo_via_edit(): void
    {
        Storage::fake('public');
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

        $path = UploadedFile::fake()->image('old.jpg')->store('photos', 'public');
        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'profile_photo_path' => $path,
            'photo_hash' => hash('sha256', (string) Storage::disk('public')->get($path)),
        ]);

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'remove_photo' => 1,
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'profile_photo_path' => null,
            'photo_hash' => null,
        ]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_authenticated_admin_can_toggle_user_status(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        /** @var User $targetUser */
        $targetUser = User::factory()->create(['status' => 'active']);

        // Toggle to suspended
        $response = $this->actingAs($admin)->post(route('system-tables.users.toggle-status', $targetUser));
        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'status' => 'suspended']);

        // Toggle back to active
        $response2 = $this->actingAs($admin)->post(route('system-tables.users.toggle-status', $targetUser));
        $response2->assertRedirect(route('system-tables.users'));
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'status' => 'active']);
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create(['status' => 'active']);

        // Cannot toggle own account
        $toggleResponse = $this->actingAs($admin)->post(route('system-tables.users.toggle-status', $admin));
        $toggleResponse->assertRedirect(route('system-tables.users'));
        $toggleResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active']);

        // Cannot update own account status to suspended
        $updateResponse = $this->actingAs($admin)->put(route('system-tables.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'status' => 'suspended',
        ]);
        $updateResponse->assertRedirect(route('system-tables.users'));
        $updateResponse->assertSessionHasErrors('status');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active']);
    }

    public function test_users_explorer_can_filter_by_account_status(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create(['name' => 'Admin Operator', 'status' => 'active']);
        User::factory()->create(['name' => 'Active Regular User', 'status' => 'active']);
        User::factory()->create(['name' => 'Suspended Member User', 'status' => 'suspended']);

        // Filter active
        $activeResponse = $this->actingAs($admin)->get(route('system-tables.users', ['status' => 'active']));
        $activeResponse->assertOk();
        $activeResponse->assertSee('Active Regular User');
        $activeResponse->assertDontSee('Suspended Member User');

        // Filter suspended
        $suspendedResponse = $this->actingAs($admin)->get(route('system-tables.users', ['status' => 'suspended']));
        $suspendedResponse->assertOk();
        $suspendedResponse->assertSee('Suspended Member User');
        $suspendedResponse->assertDontSee('Active Regular User');
    }

    public function test_default_role_cannot_be_deleted(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $defaultRole = Role::where('name', 'User')->firstOrFail();

        $response = $this->actingAs($superAdmin)->delete(route('system-tables.roles.destroy', $defaultRole));

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('roles', ['name' => 'User']);
    }

    public function test_default_role_cannot_be_renamed(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $defaultRole = Role::where('name', 'User')->firstOrFail();

        $response = $this->actingAs($superAdmin)->put(route('system-tables.roles.update', $defaultRole), [
            'name' => 'Renamed-User',
            'permissions' => ['view users'],
        ]);

        $response->assertRedirect(route('system-tables.roles'));
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('roles', ['name' => 'User']);
        $this->assertDatabaseMissing('roles', ['name' => 'Renamed-User']);
    }

    public function test_roles_view_renders_default_role_badge(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('system-tables.roles'));

        $response->assertOk();
        $response->assertSee(__('Default Role'));
    }

    public function test_admin_cannot_change_their_own_role_via_system_panel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Self-Edit Admin',
            'email' => 'self-edit@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'Admin',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHasErrors('role');

        // Role must remain Super-Admin, not changed to Admin
        $admin->refresh();
        $this->assertTrue($admin->hasRole('Super-Admin'));
        $this->assertFalse($admin->hasRole('Admin'));
    }

    public function test_admin_cannot_delete_their_own_account_via_system_panel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Self-Delete Admin',
            'email' => 'self-delete@example.com',
        ]);

        $response = $this->actingAs($admin)->delete(route('system-tables.users.destroy', $admin));

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_user_via_system_panel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();
        /** @var User $targetUser */
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('system-tables.users.destroy', $targetUser));

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_cannot_suspend_their_own_account_via_toggle(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('system-tables.users.toggle-status', $admin));

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHasErrors('error');
        $admin->refresh();
        $this->assertTrue($admin->isActive());
    }

    public function test_admin_cannot_suspend_their_own_account_via_update(): void
    {
        /** @var User $admin */
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->put(route('system-tables.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'status' => 'suspended',
        ]);

        $response->assertRedirect(route('system-tables.users'));
        $response->assertSessionHasErrors('status');
        $admin->refresh();
        $this->assertTrue($admin->isActive());
    }
}
