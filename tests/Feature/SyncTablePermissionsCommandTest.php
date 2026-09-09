<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SyncTablePermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_command_does_not_create_permissions_in_database(): void
    {
        $this->artisan('permissions:sync-tables --dry-run')
            ->expectsOutputToContain('[DRY-RUN MODE]')
            ->assertExitCode(0);

        $this->assertEquals(0, Permission::count());
    }

    public function test_command_generates_permissions_and_syncs_to_super_admin(): void
    {
        $this->artisan('permissions:sync-tables')
            ->expectsOutputToContain('Successfully generated')
            ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['name' => 'view users']);
        $this->assertDatabaseHas('permissions', ['name' => 'create users']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit users']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete users']);

        $superAdmin = Role::where('name', 'Super-Admin')->first();
        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->hasPermissionTo('view users'));
    }

    public function test_command_prunes_obsolete_permissions_for_non_existent_tables(): void
    {
        Permission::create(['name' => 'view obsolete_table', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete obsolete_table', 'guard_name' => 'web']);

        $this->artisan('permissions:sync-tables')
            ->expectsOutputToContain('Pruned 2 obsolete permissions')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('permissions', ['name' => 'view obsolete_table']);
        $this->assertDatabaseMissing('permissions', ['name' => 'delete obsolete_table']);
    }
}
