<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemTableService
{
    /**
     * Retrieve aggregated statistics for all 13 tables across the application.
     *
     * @return array<string, int>
     */
    public function getOverviewStats(): array
    {
        return [
            'users_count' => User::count(),
            'sessions_count' => $this->safeCount('sessions'),
            'roles_count' => Role::count(),
            'permissions_count' => Permission::count(),
            'activities_count' => Activity::count(),
            'notifications_count' => $this->safeCount('notifications'),
            'pending_jobs_count' => $this->safeCount('jobs'),
            'failed_jobs_count' => $this->safeCount('failed_jobs'),
            'job_batches_count' => $this->safeCount('job_batches'),
            'cache_count' => $this->safeCount('cache'),
            'cache_locks_count' => $this->safeCount('cache_locks'),
            'password_resets_count' => $this->safeCount('password_reset_tokens'),
        ];
    }

    /**
     * Retrieve recent system activities for the overview stream with translated descriptions.
     *
     * @return Collection<int, Activity>
     */
    public function getRecentActivities(int $limit = 5): Collection
    {
        return Activity::with(['causer', 'subject'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                $activity->translated_description = $this->translateActivityDescription($activity->description);

                return $activity;
            });
    }

    /**
     * Get paginated users with their assigned roles and session indicators.
     */
    public function getUsers(?string $search = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        if ($status !== null && in_array($status, ['active', 'suspended'], true)) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get paginated active sessions.
     */
    public function getSessions(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('sessions')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select([
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->orderByDesc('sessions.last_activity')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Retrieve roles with assigned permissions and users count.
     *
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return Role::with('permissions')
            ->withCount('users')
            ->get();
    }

    /**
     * Retrieve paginated permissions with associated roles count.
     */
    public function getPermissions(int $perPage = 20): LengthAwarePaginator
    {
        return Permission::withCount('roles')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Retrieve paginated activity logs with optional event filtering, keyword search, and translated descriptions.
     */
    public function getActivityLogs(?string $event = null, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject'])->latest('id');

        if ($event !== null && trim($event) !== '') {
            $query->where('event', trim($event));
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('log_name', 'like', $term)
                    ->orWhere('subject_type', 'like', $term);
            });
        }

        return $query->paginate($perPage)->through(function ($activity) {
            $activity->translated_description = $this->translateActivityDescription($activity->description);

            return $activity;
        })->withQueryString();
    }

    /**
     * Retrieve paginated database notifications.
     */
    public function getNotifications(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('notifications')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $query = DB::table('notifications')->latest('created_at');

        if ($status === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($status === 'unread') {
            $query->whereNull('read_at');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve paginated queue pending jobs.
     */
    public function getJobs(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('jobs')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('jobs')->orderBy('id')->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve paginated failed jobs with exception details.
     */
    public function getFailedJobs(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('failed_jobs')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('failed_jobs')->latest('failed_at')->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve paginated job batches.
     */
    public function getJobBatches(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('job_batches')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('job_batches')->latest('created_at')->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve paginated cache keys.
     */
    public function getCacheEntries(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('cache')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('cache')->orderBy('key')->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve active cache locks.
     */
    public function getCacheLocks(int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('cache_locks')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('cache_locks')->orderBy('key')->paginate($perPage)->withQueryString();
    }

    /**
     * Safely count rows in a table if it exists.
     */
    public function safeCount(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }

    /**
     * Translate an activity log description into the active application locale.
     */
    public function translateActivityDescription(?string $description): string
    {
        if ($description === null || trim($description) === '') {
            return '';
        }

        $trimmed = trim($description);

        // 1. Direct dictionary match
        $directTranslation = __($trimmed);
        if ($directTranslation !== $trimmed) {
            return $directTranslation;
        }

        // 2. User mutations
        if (preg_match("/^Created new system user '([^']+)'$/", $trimmed, $m)) {
            return __("Created new system user ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Updated system user '([^']+)'$/", $trimmed, $m)) {
            return __("Updated system user ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Deleted system user '([^']+)'$/", $trimmed, $m)) {
            return __("Deleted system user ':name'", ['name' => $m[1]]);
        }

        // 3. Role mutations
        if (preg_match("/^Created new system role '([^']+)'$/", $trimmed, $m)) {
            return __("Created new system role ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Updated system role '([^']+)'$/", $trimmed, $m)) {
            return __("Updated system role ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Deleted system role '([^']+)'$/", $trimmed, $m)) {
            return __("Deleted system role ':name'", ['name' => $m[1]]);
        }

        // 4. Permission mutations
        if (preg_match("/^Created new system permission '([^']+)'$/", $trimmed, $m)) {
            return __("Created new system permission ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Updated system permission '([^']+)'$/", $trimmed, $m)) {
            return __("Updated system permission ':name'", ['name' => $m[1]]);
        }
        if (preg_match("/^Deleted system permission '([^']+)'$/", $trimmed, $m)) {
            return __("Deleted system permission ':name'", ['name' => $m[1]]);
        }

        // 5. Data pruning mutations
        if (preg_match("/^Added custom table '([^']+)' to automated data pruning$/", $trimmed, $m)) {
            return __("Added custom table ':table' to automated data pruning", ['table' => $m[1]]);
        }
        if (preg_match("/^Removed custom table '([^']+)' from automated data pruning$/", $trimmed, $m)) {
            return __("Removed custom table ':table' from automated data pruning", ['table' => $m[1]]);
        }
        if (preg_match("/^Auto-pruned (\d+) records from table '([^']+)' \(Date: (\d+), Capacity: (\d+)\)$/", $trimmed, $m)) {
            return __("Auto-pruned :total records from table ':table' (Date: :date, Capacity: :capacity)", [
                'total' => $m[1],
                'table' => $m[2],
                'date' => $m[3],
                'capacity' => $m[4],
            ]);
        }

        // 6. Database backup mutations
        if (preg_match("/^Deleted backup snapshot '([^']+)'$/", $trimmed, $m)) {
            return __("Deleted backup snapshot ':file'", ['file' => $m[1]]);
        }
        if (preg_match("/^Restored database state from snapshot '([^']+)'$/", $trimmed, $m)) {
            return __("Restored database state from snapshot ':file'", ['file' => $m[1]]);
        }

        return $trimmed;
    }
<<<<<<< HEAD

    /**
     * Provision the first Super-Admin account and initialize system RBAC baseline.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function createInitialSuperAdmin(array $data): User
    {
        $needsMigration = ! Schema::hasTable('users');

        if (! $needsMigration && User::count() > 0) {
            throw new \RuntimeException('Cannot run initial setup when users already exist.');
        }

        if ($needsMigration) {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        }

        return DB::transaction(function () use ($data): User {
            /** @var PermissionDiscoveryService $discoveryService */
            $discoveryService = app(PermissionDiscoveryService::class);
            $discoveryService->generateCrudPermissionsForTables();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => AccountStatus::Active,
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles(['Super-Admin']);
            $discoveryService->syncSuperAdminPermissions();

            activity('system_setup')
                ->causedBy($user)
                ->performedOn($user)
                ->log('Initialized system and created the initial Super Admin account');

            return $user;
        });
    }
=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
}
