<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionDiscoveryService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Dynamically generate CRUD permissions for discovered database tables
        /** @var PermissionDiscoveryService $discoveryService */
        $discoveryService = app(PermissionDiscoveryService::class);
        $discoveryService->generateCrudPermissionsForTables();

        // Roles definition
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        if (Permission::where('name', 'view users')->exists()) {
            $userRole->syncPermissions(['view users']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereIn('name', ['view users', 'create users', 'edit users'])->pluck('name');
        if ($adminPermissions->isNotEmpty()) {
            $adminRole->syncPermissions($adminPermissions);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $discoveryService->syncSuperAdminPermissions();

        // Assign Super-Admin role to the first user if present
        $firstUser = User::first();
        if ($firstUser && ! $firstUser->hasRole('Super-Admin')) {
            $firstUser->assignRole($superAdminRole);
        }
    }
}
