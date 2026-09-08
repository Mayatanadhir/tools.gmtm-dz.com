<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
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

        // Standard permissions list
        $permissions = [
            'view instruments',
            'create instruments',
            'edit instruments',
            'delete instruments',
            'manage users',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Roles definition
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions(['view instruments']);

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'view instruments',
            'create instruments',
            'edit instruments',
            'delete instruments',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Assign Super-Admin role to the first user if present
        $firstUser = User::first();
        if ($firstUser && ! $firstUser->hasRole('Super-Admin')) {
            $firstUser->assignRole($superAdminRole);
        }
    }
}
