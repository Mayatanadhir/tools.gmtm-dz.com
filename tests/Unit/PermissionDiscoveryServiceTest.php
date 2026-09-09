<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PermissionDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionDiscoveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionDiscoveryService;
    }

    public function test_it_discovers_business_tables_and_excludes_system_blacklist(): void
    {
        $tables = $this->service->getDiscoveredTables();

        $this->assertIsArray($tables);
        $this->assertContains('users', $tables);

        // Ensure non-existent tables like 'instruments' are NOT discovered
        $this->assertNotContains('instruments', $tables);

        // Ensure blacklisted tables are strictly excluded
        $this->assertNotContains('migrations', $tables);
        $this->assertNotContains('sessions', $tables);
        $this->assertNotContains('cache', $tables);
        $this->assertNotContains('cache_locks', $tables);
        $this->assertNotContains('jobs', $tables);
        $this->assertNotContains('failed_jobs', $tables);
        $this->assertNotContains('activity_log', $tables);
        $this->assertNotContains('roles', $tables);
        $this->assertNotContains('permissions', $tables);
    }

    public function test_it_generates_standard_crud_permissions_for_tables(): void
    {
        $result = $this->service->generateCrudPermissionsForTables(['users']);

        $this->assertContains('view users', $result['permissions_created']);
        $this->assertContains('create users', $result['permissions_created']);
        $this->assertContains('edit users', $result['permissions_created']);
        $this->assertContains('delete users', $result['permissions_created']);

        $this->assertDatabaseHas('permissions', ['name' => 'view users']);
        $this->assertDatabaseHas('permissions', ['name' => 'create users']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit users']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete users']);
    }

    public function test_it_syncs_all_permissions_to_super_admin_role(): void
    {
        Permission::create(['name' => 'view test_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'create test_item', 'guard_name' => 'web']);

        $count = $this->service->syncSuperAdminPermissions();

        $this->assertGreaterThanOrEqual(2, $count);

        $superAdmin = Role::where('name', 'Super-Admin')->first();
        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->hasPermissionTo('view test_item'));
        $this->assertTrue($superAdmin->hasPermissionTo('create test_item'));
    }

    public function test_it_builds_grouped_permission_matrix(): void
    {
        Permission::create(['name' => 'view devices', 'guard_name' => 'web']);
        Permission::create(['name' => 'create devices', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit devices', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete devices', 'guard_name' => 'web']);
        Permission::create(['name' => 'special_audit_access', 'guard_name' => 'web']);

        $matrix = $this->service->getGroupedPermissionMatrix(autoSync: false);

        $this->assertArrayHasKey('entities', $matrix);
        $this->assertArrayHasKey('devices', $matrix['entities']);
        $this->assertEquals('view devices', $matrix['entities']['devices']['view']);
        $this->assertEquals('create devices', $matrix['entities']['devices']['create']);
        $this->assertEquals('edit devices', $matrix['entities']['devices']['edit']);
        $this->assertEquals('delete devices', $matrix['entities']['devices']['delete']);

        $this->assertArrayHasKey('custom', $matrix);
        $this->assertContains('special_audit_access', $matrix['custom']);
    }

    public function test_it_automatically_syncs_permissions_when_building_matrix_with_autosync(): void
    {
        // Initially no permissions exist for 'users'
        $this->assertEquals(0, Permission::count());

        $matrix = $this->service->getGroupedPermissionMatrix(autoSync: true);

        // Permissions should have been automatically generated for live 'users' table
        $this->assertDatabaseHas('permissions', ['name' => 'view users']);
        $this->assertDatabaseHas('permissions', ['name' => 'create users']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit users']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete users']);

        $this->assertArrayHasKey('users', $matrix['entities']);
        // 'instruments' does not exist in schema, so it must NOT be in the matrix
        $this->assertArrayNotHasKey('instruments', $matrix['entities']);

        // Super-Admin should automatically have all permissions attached
        $superAdmin = Role::where('name', 'Super-Admin')->first();
        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->hasPermissionTo('view users'));
    }

    public function test_it_prunes_stale_permissions_for_non_existent_tables(): void
    {
        // Seed stale permissions for a non-existent table (e.g. instruments)
        Permission::create(['name' => 'view instruments', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete instruments', 'guard_name' => 'web']);

        $pruned = $this->service->pruneStaleCrudPermissions(['users']);

        $this->assertContains('view instruments', $pruned);
        $this->assertContains('delete instruments', $pruned);
        $this->assertDatabaseMissing('permissions', ['name' => 'view instruments']);
        $this->assertDatabaseMissing('permissions', ['name' => 'delete instruments']);
    }

    public function test_it_strictly_excludes_blacklisted_entities_from_matrix(): void
    {
        // Even if permissions for a blacklisted entity exist in database
        Permission::create(['name' => 'view cache', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete migrations', 'guard_name' => 'web']);

        $matrix = $this->service->getGroupedPermissionMatrix(autoSync: false);

        $this->assertArrayNotHasKey('cache', $matrix['entities']);
        $this->assertArrayNotHasKey('migrations', $matrix['entities']);
    }

    public function test_it_provides_super_roles_configuration_and_checks(): void
    {
        $this->assertEquals(['Super-Admin'], $this->service->getSuperRoles());
        $this->assertTrue($this->service->isSuperRole('Super-Admin'));
        $this->assertFalse($this->service->isSuperRole('Admin'));
        $this->assertFalse($this->service->isSuperRole('User'));
    }

    public function test_it_provides_default_role_configuration_and_checks(): void
    {
        $this->assertEquals('User', $this->service->getDefaultRole());
        $this->assertTrue($this->service->isDefaultRole('User'));
        $this->assertFalse($this->service->isDefaultRole('Admin'));
        $this->assertFalse($this->service->isDefaultRole('Super-Admin'));
    }
}
