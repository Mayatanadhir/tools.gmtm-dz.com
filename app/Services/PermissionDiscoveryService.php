<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionDiscoveryService
{
    /**
     * Strict system blacklist of internal forensic, queue, and RBAC tables.
     *
     * @var array<int, string>
     */
    public const array SYSTEM_BLACKLIST = [
        'migrations',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'job_batches',
        'activity_log',
        'notifications',
        'system_settings',
        'roles',
        'permissions',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'permission_categories',
    ];

    /**
     * Standard CRUD action verbs.
     *
     * @var array<int, string>
     */
    public const array CRUD_ACTIONS = [
        'view',
        'create',
        'edit',
        'delete',
    ];

    /**
     * الأدوار الخارقة التي تتجاوز كل الصلاحيات
     *
     * @var array<int, string>
     */
    protected array $superRoles = ['Super-Admin'];

    /**
     * الدور الافتراضي الأساسي الممنوح تلقائياً للمستخدمين الجدد
     */
    protected string $defaultRole = 'User';

    /**
     * Get the list of super roles.
     *
     * @return array<int, string>
     */
    public function getSuperRoles(): array
    {
        return $this->superRoles;
    }

    /**
     * Check if a given role name is a super role.
     */
    public function isSuperRole(string $roleName): bool
    {
        return in_array($roleName, $this->superRoles, true);
    }

    /**
     * Get the name of the default baseline role.
     */
    public function getDefaultRole(): string
    {
        return $this->defaultRole;
    }

    /**
     * Check if a given role name is the system default baseline role.
     */
    public function isDefaultRole(string $roleName): bool
    {
        return $roleName === $this->defaultRole;
    }

    /**
     * Get the translated functional title for a given role name.
     */
    public function getRoleFunctionalTitle(string $roleName): string
    {
        return match ($roleName) {
            'Super-Admin' => __('Super Administrator (Full Sovereign Access)'),
            'Admin' => __('System Administrator (Operations & Users)'),
            'User' => __('Standard User (General Workspace Services)'),
            default => __($roleName),
        };
    }

    /**
     * Get the translated functional scope description for a given role name.
     */
    public function getRoleFunctionalScope(string $roleName): string
    {
        return match ($roleName) {
            'Super-Admin' => __('Full sovereign authority over system configurations, forensic tables, and all application data.'),
            'Admin' => __('Operational and user management with access to standard business tables, excluding security-critical tables.'),
            'User' => __('Access to general application tools and profile workspace without administrative privileges.'),
            default => __('Custom user role with assigned granular table privileges.'),
        };
    }

    /**
     * Discover business tables from the active database schema, excluding blacklisted system tables.
     *
     * @return array<int, string>
     */
    public function getDiscoveredTables(?string $connection = null): array
    {
        $db = DB::connection($connection);
        $currentDatabase = $db->getDatabaseName();
        $discovered = [];

        try {
            $tables = Schema::connection($connection)->getTables();

            foreach ($tables as $tableInfo) {
                $tableName = (string) $tableInfo['name'];
                $tableSchema = (string) ($tableInfo['schema'] ?? '');

                // Ensure table belongs to the current database if schema info is present on MySQL
                $driver = $db->getDriverName();
                if ($driver === 'mysql' && $tableSchema !== '' && $tableSchema !== $currentDatabase) {
                    continue;
                }

                if (! in_array(strtolower($tableName), self::SYSTEM_BLACKLIST, true)) {
                    $discovered[] = strtolower($tableName);
                }
            }
        } catch (\Throwable) {
            // Fallback for drivers or configurations that do not support getTables()
            $allTables = Schema::connection($connection)->getTableListing();
            foreach ($allTables as $rawName) {
                $cleanName = strtolower(basename(str_replace('.', '/', (string) $rawName)));
                if (! in_array($cleanName, self::SYSTEM_BLACKLIST, true)) {
                    $discovered[] = $cleanName;
                }
            }
        }

        $discovered = array_values(array_unique($discovered));
        sort($discovered);

        return $discovered;
    }

    /**
     * Remove standard CRUD permissions for entities that no longer exist in the active database schema.
     *
     * @param  array<int, string>  $validTables
     * @return array<int, string> List of pruned permission names
     */
    public function pruneStaleCrudPermissions(array $validTables): array
    {
        $allPermissions = Permission::all();
        $pruned = [];

        foreach ($allPermissions as $permission) {
            $name = (string) $permission->name;

            if (preg_match('/^(view|create|edit|delete)\s+(.+)$/i', $name, $matches)) {
                $entity = strtolower(trim($matches[2]));

                if (! in_array($entity, $validTables, true)) {
                    $permission->delete();
                    $pruned[] = $name;
                }
            }
        }

        return $pruned;
    }

    /**
     * Generate standard CRUD permissions for discovered or specified tables and prune obsolete permissions.
     *
     * @param  array<int, string>|null  $tables
     * @return array{
     *     tables_scanned: array<int, string>,
     *     permissions_created: array<int, string>,
     *     permissions_pruned: array<int, string>,
     *     total_active_permissions: int
     * }
     */
    public function generateCrudPermissionsForTables(?array $tables = null): array
    {
        $tablesToProcess = $tables ?? $this->getDiscoveredTables();
        $pruned = $this->pruneStaleCrudPermissions($tablesToProcess);
        $created = [];

        foreach ($tablesToProcess as $table) {
            foreach (self::CRUD_ACTIONS as $action) {
                $permName = "{$action} {$table}";
                $permission = Permission::firstOrCreate([
                    'name' => $permName,
                    'guard_name' => 'web',
                ]);

                if ($permission->wasRecentlyCreated) {
                    $created[] = $permName;
                }
            }
        }

        $missingSuperRole = false;
        foreach ($this->superRoles as $superRoleName) {
            if (! Role::where('name', $superRoleName)->exists()) {
                $missingSuperRole = true;
                break;
            }
        }

        if (! empty($created) || ! empty($pruned) || $missingSuperRole) {
            // Flush Spatie permission cache to ensure instant activation
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // Ensure Super-Admin and super roles inherit all active permissions
            $this->syncSuperAdminPermissions();
        }

        return [
            'tables_scanned' => $tablesToProcess,
            'permissions_created' => $created,
            'permissions_pruned' => $pruned,
            'total_active_permissions' => Permission::count(),
        ];
    }

    /**
     * Ensure the Super-Admin and all super roles exist and hold all permissions in the system.
     */
    public function syncSuperAdminPermissions(): int
    {
        $allPermissions = Permission::all();

        foreach ($this->superRoles as $superRoleName) {
            $role = Role::firstOrCreate([
                'name' => $superRoleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($allPermissions);
        }

        return $allPermissions->count();
    }

    /**
     * Build an interactive permission matrix grouped by entity and CRUD actions.
     * Automatically introspects schema and ensures permissions exist for all discovered tables.
     *
     * @param  bool  $autoSync  Whether to automatically generate missing permissions for discovered tables
     * @return array{
     *     entities: array<string, array<string, string>>,
     *     custom: array<int, string>,
     *     actions: array<int, string>,
     *     all_names: array<int, string>
     * }
     */
    public function getGroupedPermissionMatrix(bool $autoSync = true): array
    {
        if ($autoSync) {
            $this->generateCrudPermissionsForTables();
        }

        $permissions = Permission::orderBy('name')->get();
        $entities = [];
        $custom = [];
        $allNames = [];

        foreach ($permissions as $perm) {
            $name = (string) $perm->name;
            $allNames[] = $name;

            if (preg_match('/^(view|create|edit|delete)\s+(.+)$/i', $name, $matches)) {
                $action = strtolower($matches[1]);
                $entity = strtolower(trim($matches[2]));

                // Double check that blacklisted entities are never included in the matrix
                if (in_array($entity, self::SYSTEM_BLACKLIST, true)) {
                    continue;
                }

                if (! isset($entities[$entity])) {
                    $entities[$entity] = [];
                }
                $entities[$entity][$action] = $name;
            } else {
                $custom[] = $name;
            }
        }

        ksort($entities);
        sort($custom);

        return [
            'entities' => $entities,
            'custom' => $custom,
            'actions' => self::CRUD_ACTIONS,
            'all_names' => $allNames,
        ];
    }
}
