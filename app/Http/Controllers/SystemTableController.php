<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddPruningTableRequest;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\CreateSystemUserRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Requests\UpdatePruningSettingsRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\UpdateSystemUserRequest;
use App\Models\User;
use App\Services\DatabaseBackupService;
use App\Services\DataPruningService;
use App\Services\SystemTableService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemTableController extends Controller
{
    public function __construct(
        protected SystemTableService $systemTableService,
        protected DataPruningService $dataPruningService,
        protected DatabaseBackupService $databaseBackupService,
        protected UserService $userService
    ) {}

    /**
     * Display the System Tables Explorer overview dashboard.
     */
    public function index(): View
    {
        $stats = $this->systemTableService->getOverviewStats();
        $recentActivities = $this->systemTableService->getRecentActivities(6);

        return view('system.index', compact('stats', 'recentActivities'));
    }

    /**
     * Display Users and Active Sessions.
     */
    public function users(Request $request): View
    {
        $search = $request->query('search');
        $users = $this->systemTableService->getUsers(is_string($search) ? $search : null);
        $sessions = $this->systemTableService->getSessions();
        $roles = $this->systemTableService->getRoles();

        return view('system.users', compact('users', 'sessions', 'search', 'roles'));
    }

    /**
     * Create a new user from the system explorer.
     */
    public function storeUser(CreateSystemUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $this->userService->register([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if (! empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        activity('system_users')
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'] ?? null,
            ])
            ->log("Created new system user '{$user->name}'");

        return redirect()
            ->route('system-tables.users')
            ->with('status', __('User :name created successfully.', ['name' => $user->name]));
    }

    /**
     * Update an existing user from the system explorer.
     */
    public function updateUser(UpdateSystemUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (array_key_exists('role', $validated)) {
            $user->syncRoles(! empty($validated['role']) ? [$validated['role']] : []);
        }

        activity('system_users')
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'] ?? null,
            ])
            ->log("Updated system user '{$user->name}'");

        return redirect()
            ->route('system-tables.users')
            ->with('status', __('User :name updated successfully.', ['name' => $user->name]));
    }

    /**
     * Display Roles and Permissions matrix.
     */
    public function roles(): View
    {
        $roles = $this->systemTableService->getRoles();
        $permissions = $this->systemTableService->getPermissions();
        $allPermissions = Permission::orderBy('name')->get();

        return view('system.roles', compact('roles', 'permissions', 'allPermissions'));
    }

    /**
     * Create a new role with assigned permissions.
     */
    public function storeRole(CreateRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        activity('roles_permissions')
            ->performedOn($role)
            ->withProperties([
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions' => $validated['permissions'] ?? [],
            ])
            ->log("Created new system role '{$role->name}'");

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Role :name created successfully.', ['name' => $role->name]));
    }

    /**
     * Create a new permission in the system catalog.
     */
    public function storePermission(CreatePermissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        activity('roles_permissions')
            ->performedOn($permission)
            ->withProperties([
                'permission_id' => $permission->id,
                'name' => $permission->name,
            ])
            ->log("Created new system permission '{$permission->name}'");

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Permission :name created successfully.', ['name' => $permission->name]));
    }

    /**
     * Update an existing role and its assigned permissions.
     */
    public function updateRole(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        $oldName = $role->name;
        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        activity('roles_permissions')
            ->performedOn($role)
            ->withProperties([
                'role_id' => $role->id,
                'old_name' => $oldName,
                'new_name' => $role->name,
                'permissions' => $validated['permissions'] ?? [],
            ])
            ->log("Updated system role '{$role->name}'");

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Role :name updated successfully.', ['name' => $role->name]));
    }

    /**
     * Update an existing permission in the system catalog.
     */
    public function updatePermission(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validated();

        $oldName = $permission->name;
        $permission->update([
            'name' => $validated['name'],
        ]);

        activity('roles_permissions')
            ->performedOn($permission)
            ->withProperties([
                'permission_id' => $permission->id,
                'old_name' => $oldName,
                'new_name' => $permission->name,
            ])
            ->log("Updated system permission '{$permission->name}'");

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Permission :name updated successfully.', ['name' => $permission->name]));
    }

    /**
     * Delete a role from the system RBAC catalog.
     */
    public function destroyRole(Role $role): RedirectResponse
    {
        $roleName = $role->name;

        activity('roles_permissions')
            ->performedOn($role)
            ->withProperties([
                'role_id' => $role->id,
                'name' => $roleName,
                'users_count' => $role->users()->count(),
            ])
            ->log("Deleted system role '{$roleName}'");

        $role->delete();

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Role :name deleted successfully.', ['name' => $roleName]));
    }

    /**
     * Delete a permission from the system access catalog.
     */
    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $permissionName = $permission->name;

        activity('roles_permissions')
            ->performedOn($permission)
            ->withProperties([
                'permission_id' => $permission->id,
                'name' => $permissionName,
            ])
            ->log("Deleted system permission '{$permissionName}'");

        $permission->delete();

        return redirect()
            ->route('system-tables.roles')
            ->with('status', __('Permission :name deleted successfully.', ['name' => $permissionName]));
    }

    /**
     * Display the Activity Log audit trail.
     */
    public function activityLog(Request $request): View
    {
        $event = $request->query('event');
        $search = $request->query('search');

        $activities = $this->systemTableService->getActivityLogs(
            is_string($event) ? $event : null,
            is_string($search) ? $search : null
        );

        return view('system.activity-log', compact('activities', 'event', 'search'));
    }

    /**
     * Display Notifications.
     */
    public function notifications(Request $request): View
    {
        $status = $request->query('status');
        $notifications = $this->systemTableService->getNotifications(
            is_string($status) ? $status : null
        );

        return view('system.notifications', compact('notifications', 'status'));
    }

    /**
     * Display Queues, Failed Jobs, and Job Batches.
     */
    public function queues(Request $request): View
    {
        $tab = $request->query('tab', 'jobs');
        $jobs = $this->systemTableService->getJobs();
        $failedJobs = $this->systemTableService->getFailedJobs();
        $batches = $this->systemTableService->getJobBatches();

        return view('system.queues', compact('jobs', 'failedJobs', 'batches', 'tab'));
    }

    /**
     * Display Cache entries and Cache Locks.
     */
    public function cache(): View
    {
        $cacheEntries = $this->systemTableService->getCacheEntries();
        $cacheLocks = $this->systemTableService->getCacheLocks();

        return view('system.cache', compact('cacheEntries', 'cacheLocks'));
    }

    /**
     * Display Data Pruning and Lifecycle Management settings dashboard.
     */
    public function pruningSettings(): View
    {
        $effectiveConfig = $this->dataPruningService->getEffectiveConfig();
        $hasCustomSettings = $this->dataPruningService->hasCustomSettings();
        $history = $this->dataPruningService->getPruningHistory(10);
        $eligibleTables = $this->dataPruningService->getEligibleTablesForPruning();

        $counts = [];
        foreach (array_keys((array) ($effectiveConfig['tables'] ?? [])) as $tblKey) {
            $counts[$tblKey] = $this->systemTableService->safeCount($tblKey);
        }

        return view('system.pruning', compact('effectiveConfig', 'hasCustomSettings', 'history', 'counts', 'eligibleTables'));
    }

    /**
     * Update custom pruning lifecycle settings in database storage.
     */
    public function updatePruningSettings(UpdatePruningSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'enabled' => $request->boolean('enabled'),
            'chunk_size' => (int) $validated['chunk_size'],
            'tables' => [],
        ];

        foreach ($validated['tables'] as $tableKey => $tableData) {
            $data['tables'][$tableKey] = [
                'enabled' => ! empty($tableData['enabled']),
                'retention_days' => (int) $tableData['retention_days'],
                'max_records' => (int) $tableData['max_records'],
            ];

            if ($tableKey === 'notifications') {
                $data['tables'][$tableKey]['only_read'] = $request->boolean('tables.notifications.only_read');
            }
        }

        $this->dataPruningService->saveCustomSettings($data);

        return redirect()
            ->route('system-tables.pruning')
            ->with('status', __('Pruning lifecycle settings updated successfully.'));
    }

    /**
     * Run a dry-run pruning simulation and return simulated results.
     */
    public function dryRunPruning(Request $request): JsonResponse|RedirectResponse
    {
        $report = $this->dataPruningService->pruneAll(dryRun: true);

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return redirect()
            ->route('system-tables.pruning')
            ->with('dry_run_report', $report);
    }

    /**
     * Trigger immediate automated pruning execution.
     */
    public function executePruning(): RedirectResponse
    {
        $report = $this->dataPruningService->pruneAll(dryRun: false);

        return redirect()
            ->route('system-tables.pruning')
            ->with('status', __('Automated pruning executed. Total records purged: :count', ['count' => $report['total_pruned']]));
    }

    /**
     * Reset custom pruning settings back to configuration defaults.
     */
    public function resetPruningSettings(): RedirectResponse
    {
        $this->dataPruningService->resetCustomSettings();

        return redirect()
            ->route('system-tables.pruning')
            ->with('status', __('Pruning settings have been reset to configuration defaults.'));
    }

    /**
     * Add a dynamic database table to automated data pruning.
     */
    public function addPruningTable(AddPruningTableRequest $request): RedirectResponse
    {
        try {
            /** @var array{table: string, primary_key: string, date_column: string, retention_days: int, max_records: int, enabled?: bool} $data */
            $data = $request->validated();
            $this->dataPruningService->addCustomTable($data);

            return redirect()
                ->route('system-tables.pruning')
                ->with('status', __('Table :table successfully onboarded to automated pruning.', ['table' => $request->validated('table')]));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('system-tables.pruning')
                ->withErrors(['table' => $e->getMessage()]);
        }
    }

    /**
     * Remove a custom table from automated data pruning.
     */
    public function removePruningTable(string $table): RedirectResponse
    {
        try {
            $this->dataPruningService->removeCustomTable($table);

            return redirect()
                ->route('system-tables.pruning')
                ->with('status', __('Table :table successfully removed from automated pruning.', ['table' => $table]));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('system-tables.pruning')
                ->withErrors(['table' => $e->getMessage()]);
        }
    }

    /**
     * Display Database Backups management dashboard.
     */
    public function backups(): View
    {
        $backupData = $this->databaseBackupService->getBackups();

        return view('system.backups', compact('backupData'));
    }

    /**
     * Create a new database backup snapshot.
     */
    public function createBackup(): RedirectResponse
    {
        try {
            $result = $this->databaseBackupService->createBackup(onlyDb: true);

            return redirect()
                ->route('system-tables.backups')
                ->with('status', $result['message']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('system-tables.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }
    }

    /**
     * Download a database backup snapshot.
     */
    public function downloadBackup(string $file): BinaryFileResponse|RedirectResponse
    {
        try {
            $cleanName = $this->databaseBackupService->sanitizeFileName($file);
            $relativePath = $this->databaseBackupService->resolveBackupRelativePath($cleanName);
            $fullPath = Storage::disk('local')->path($relativePath);

            return response()->download($fullPath, $cleanName, [
                'Content-Type' => 'application/zip',
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('system-tables.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }
    }

    /**
     * Delete a database backup snapshot.
     */
    public function deleteBackup(string $file): RedirectResponse
    {
        try {
            $this->databaseBackupService->deleteBackup($file);

            return redirect()
                ->route('system-tables.backups')
                ->with('status', __('Backup snapshot :file successfully deleted.', ['file' => $file]));
        } catch (\Throwable $e) {
            return redirect()
                ->route('system-tables.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }
    }

    /**
     * Restore database from a specific backup snapshot.
     */
    public function restoreBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'string'],
        ]);

        $file = (string) $request->input('file');

        try {
            $result = $this->databaseBackupService->restoreBackup($file);

            return redirect()
                ->route('system-tables.backups')
                ->with('status', $result['message']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('system-tables.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }
    }

    /**
     * Restore database from the oldest available backup snapshot.
     */
    public function restoreOldestBackup(): RedirectResponse
    {
        try {
            $result = $this->databaseBackupService->restoreOldestBackup();

            return redirect()
                ->route('system-tables.backups')
                ->with('status', $result['message']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('system-tables.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }
    }
}
