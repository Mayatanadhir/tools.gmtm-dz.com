<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Spatie\Activitylog\Models\Activity;

class DataPruningService
{
    /**
     * Immutable blacklist of sovereign tables that can NEVER be pruned.
     * Hardcoded safeguard to guarantee zero data loss on critical system entities.
     *
     * @var array<int, string>
     */
    private const array IMMUTABLE_PROTECTED_TABLES = [
        'users',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'migrations',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
    ];

    /**
     * Retrieve the effective configuration merged with database-stored user overrides.
     *
     * @return array<string, mixed>
     */
    public function getEffectiveConfig(): array
    {
        /** @var array<string, mixed> $baseConfig */
        $baseConfig = (array) config('pruning', []);

        // Mark built-in tables as not custom
        if (isset($baseConfig['tables']) && is_array($baseConfig['tables'])) {
            foreach ($baseConfig['tables'] as $k => $v) {
                $baseConfig['tables'][$k]['is_custom'] = false;
            }
        }

        /** @var array<string, mixed>|null $custom */
        $custom = SystemSetting::get('data_pruning_settings');

        if ($custom === null || ! is_array($custom)) {
            return $baseConfig;
        }

        $merged = $baseConfig;

        if (isset($custom['enabled'])) {
            $merged['enabled'] = (bool) $custom['enabled'];
        }

        if (isset($custom['chunk_size'])) {
            $merged['chunk_size'] = (int) $custom['chunk_size'];
        }

        if (isset($custom['tables']) && is_array($custom['tables'])) {
            foreach ($custom['tables'] as $tableKey => $tableOverrides) {
                if (isset($merged['tables'][$tableKey]) && is_array($tableOverrides)) {
                    foreach (['enabled', 'retention_days', 'max_records', 'only_read'] as $field) {
                        if (array_key_exists($field, $tableOverrides)) {
                            $merged['tables'][$tableKey][$field] = $tableOverrides[$field];
                        }
                    }
                } elseif (is_array($tableOverrides)) {
                    // Custom dynamically onboarded table
                    $tableOverrides['is_custom'] = true;
                    $merged['tables'][$tableKey] = $tableOverrides;
                }
            }
        }

        return $merged;
    }

    /**
     * Save custom pruning settings to database and record an audit log.
     *
     * @param  array<string, mixed>  $settings
     */
    public function saveCustomSettings(array $settings): void
    {
        /** @var array<string, mixed> $existing */
        $existing = (array) (SystemSetting::get('data_pruning_settings') ?? []);

        // Preserve metadata for custom dynamically added tables
        if (isset($existing['tables']) && is_array($existing['tables'])) {
            foreach ($existing['tables'] as $tblKey => $tblData) {
                if (! empty($tblData['is_custom']) && isset($settings['tables'][$tblKey])) {
                    $settings['tables'][$tblKey]['table'] = $tblData['table'] ?? $tblKey;
                    $settings['tables'][$tblKey]['primary_key'] = $tblData['primary_key'] ?? 'id';
                    $settings['tables'][$tblKey]['date_column'] = $tblData['date_column'] ?? 'created_at';
                    $settings['tables'][$tblKey]['is_custom'] = true;
                }
            }
        }

        SystemSetting::set('data_pruning_settings', $settings, 'pruning', 'Data pruning custom lifecycle parameters');

        activity('data_pruning')
            ->withProperties($settings)
            ->log('Updated automated data pruning settings');
    }

    /**
     * Reset custom pruning settings back to configuration defaults.
     */
    public function resetCustomSettings(): void
    {
        SystemSetting::forget('data_pruning_settings');

        activity('data_pruning')
            ->log('Reset automated data pruning settings to defaults');
    }

    /**
     * Determine if custom pruning settings are currently active in database storage.
     */
    public function hasCustomSettings(): bool
    {
        return SystemSetting::where('key', 'data_pruning_settings')->exists();
    }

    /**
     * Retrieve recent pruning audit activity records.
     *
     * @return Collection<int, Activity>
     */
    public function getPruningHistory(int $limit = 10): Collection
    {
        return Activity::where('log_name', 'data_pruning')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieve all table names in the active database.
     *
     * @return array<int, string>
     */
    public function getAvailableDatabaseTables(): array
    {
        if (DB::getDriverName() === 'sqlite') {
            $tables = Schema::getTableListing();
        } else {
            $dbName = DB::getDatabaseName();
            /** @var array<int, object> $results */
            $results = DB::select("SHOW TABLES FROM `{$dbName}`");
            $prop = "Tables_in_{$dbName}";
            $tables = array_map(fn (object $row): string => (string) ($row->$prop ?? reset($row)), $results);
        }

        $normalized = array_map(function (string $tbl): string {
            $trimmed = trim($tbl);
            if (str_contains($trimmed, '.')) {
                $parts = explode('.', $trimmed);

                return end($parts);
            }

            return $trimmed;
        }, $tables);

        return array_values(array_unique($normalized));
    }

    /**
     * Discover database tables that are eligible to be onboarded for automated pruning.
     * Filters out sovereign protected tables and already configured tables.
     *
     * @return array<int, array{
     *     name: string,
     *     columns: array<int, string>,
     *     suggested_pk: string,
     *     suggested_date: string,
     *     count: int
     * }>
     */
    public function getEligibleTablesForPruning(): array
    {
        $allDbTables = $this->getAvailableDatabaseTables();
        $effectiveConfig = $this->getEffectiveConfig();
        $configuredTables = array_keys((array) ($effectiveConfig['tables'] ?? []));

        $eligible = [];
        foreach ($allDbTables as $tableName) {
            $cleanName = trim($tableName);

            // Exclude already configured tables or sovereign protected tables
            if (in_array($cleanName, $configuredTables, true) || $this->isTableProtected($cleanName)) {
                continue;
            }

            if (! Schema::hasTable($cleanName)) {
                continue;
            }

            $columns = Schema::getColumnListing($cleanName);
            if (empty($columns)) {
                continue;
            }

            // Suggest Primary Key
            $suggestedPk = in_array('id', $columns, true) ? 'id' : ($columns[0] ?? 'id');

            // Suggest Date Column
            $suggestedDate = null;
            foreach (['created_at', 'failed_at', 'logged_at', 'record_date', 'timestamp', 'updated_at'] as $candidate) {
                if (in_array($candidate, $columns, true)) {
                    $suggestedDate = $candidate;
                    break;
                }
            }

            $count = 0;
            try {
                $count = DB::table($cleanName)->count();
            } catch (\Throwable) {
                // Ignore query failure on edge-case system/view tables
            }

            $eligible[] = [
                'name' => $cleanName,
                'columns' => $columns,
                'suggested_pk' => $suggestedPk,
                'suggested_date' => $suggestedDate ?? ($columns[1] ?? $suggestedPk),
                'count' => $count,
            ];
        }

        // Sort alphabetically by table name
        usort($eligible, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $eligible;
    }

    /**
     * Add a custom database table to automated data pruning.
     *
     * @param  array{
     *     table: string,
     *     primary_key: string,
     *     date_column: string,
     *     retention_days: int,
     *     max_records: int,
     *     enabled?: bool
     * }  $data
     */
    public function addCustomTable(array $data): void
    {
        $tableName = trim((string) $data['table']);

        if ($this->isTableProtected($tableName)) {
            throw new InvalidArgumentException("Security Violation: Table '{$tableName}' is sovereign/protected and cannot be added to pruning.");
        }

        if (! Schema::hasTable($tableName)) {
            throw new InvalidArgumentException("Database table '{$tableName}' does not exist.");
        }

        $columns = Schema::getColumnListing($tableName);
        $primaryKey = trim((string) ($data['primary_key'] ?? 'id'));
        $dateColumn = trim((string) ($data['date_column'] ?? 'created_at'));

        if (! in_array($primaryKey, $columns, true)) {
            throw new InvalidArgumentException("Primary key column '{$primaryKey}' does not exist on table '{$tableName}'.");
        }

        if (! in_array($dateColumn, $columns, true)) {
            throw new InvalidArgumentException("Date column '{$dateColumn}' does not exist on table '{$tableName}'.");
        }

        /** @var array<string, mixed> $custom */
        $custom = (array) (SystemSetting::get('data_pruning_settings') ?? []);
        if (! isset($custom['tables']) || ! is_array($custom['tables'])) {
            $custom['tables'] = [];
        }

        $custom['tables'][$tableName] = [
            'enabled' => (bool) ($data['enabled'] ?? true),
            'table' => $tableName,
            'primary_key' => $primaryKey,
            'date_column' => $dateColumn,
            'retention_days' => max(0, (int) ($data['retention_days'] ?? 30)),
            'max_records' => max(0, (int) ($data['max_records'] ?? 10000)),
            'is_custom' => true,
        ];

        SystemSetting::set('data_pruning_settings', $custom, 'pruning', 'Data pruning custom lifecycle parameters');

        activity('data_pruning')
            ->withProperties($custom['tables'][$tableName])
            ->log("Added custom table '{$tableName}' to automated data pruning");
    }

    /**
     * Remove a custom-added table from data pruning configuration.
     */
    public function removeCustomTable(string $table): void
    {
        $tableName = trim($table);

        // Do not allow removing default config tables
        $baseTables = array_keys((array) config('pruning.tables', []));
        if (in_array($tableName, $baseTables, true)) {
            throw new InvalidArgumentException("Built-in system table '{$tableName}' cannot be removed, but can be disabled.");
        }

        /** @var array<string, mixed>|null $custom */
        $custom = SystemSetting::get('data_pruning_settings');
        if ($custom === null || ! is_array($custom) || ! isset($custom['tables'][$tableName])) {
            throw new InvalidArgumentException("Custom table '{$tableName}' is not configured in database settings.");
        }

        unset($custom['tables'][$tableName]);

        SystemSetting::set('data_pruning_settings', $custom, 'pruning', 'Data pruning custom lifecycle parameters');

        activity('data_pruning')
            ->withProperties(['table' => $tableName])
            ->log("Removed custom table '{$tableName}' from automated data pruning");
    }

    /**
     * Check if data pruning is globally enabled in effective configuration.
     */
    public function isGloballyEnabled(): bool
    {
        $config = $this->getEffectiveConfig();

        return (bool) ($config['enabled'] ?? true);
    }

    /**
     * Determine if a given table is sovereign/protected from pruning.
     */
    public function isTableProtected(string $table): bool
    {
        $clean = strtolower(trim($table));
        if (str_contains($clean, '.')) {
            $parts = explode('.', $clean);
            $clean = end($parts);
        }

        $effectiveConfig = $this->getEffectiveConfig();

        /** @var array<int, string> $configProtected */
        $configProtected = (array) ($effectiveConfig['protected_tables'] ?? []);
        $allProtected = array_unique(array_merge(self::IMMUTABLE_PROTECTED_TABLES, $configProtected));

        return in_array($clean, array_map('strtolower', $allProtected), true);
    }

    /**
     * Prune all configured and enabled tables.
     *
     * @return array{
     *     enabled: bool,
     *     dry_run: bool,
     *     tables: array<string, array<string, mixed>>,
     *     total_pruned: int,
     *     status: string,
     *     message?: string
     * }
     */
    public function pruneAll(bool $dryRun = false, ?int $chunkSize = null): array
    {
        if (! $this->isGloballyEnabled()) {
            return [
                'enabled' => false,
                'dry_run' => $dryRun,
                'tables' => [],
                'total_pruned' => 0,
                'status' => 'disabled',
                'message' => 'Data pruning is globally disabled in configuration.',
            ];
        }

        $effectiveConfig = $this->getEffectiveConfig();

        /** @var array<string, array<string, mixed>> $tablesConfig */
        $tablesConfig = (array) ($effectiveConfig['tables'] ?? []);
        $results = [];
        $totalPruned = 0;

        foreach ($tablesConfig as $tableKey => $tableConfig) {
            if (! empty($tableConfig['enabled'])) {
                $tableResult = $this->pruneTable($tableKey, $dryRun, $chunkSize);
                $results[$tableKey] = $tableResult;
                $totalPruned += (int) ($tableResult['total_pruned'] ?? 0);
            }
        }

        return [
            'enabled' => true,
            'dry_run' => $dryRun,
            'tables' => $results,
            'total_pruned' => $totalPruned,
            'status' => 'completed',
        ];
    }

    /**
     * Prune a specific table according to its configured lifecycle rules.
     *
     * @return array{
     *     table: string,
     *     enabled: bool,
     *     dry_run: bool,
     *     date_pruned: int,
     *     count_pruned: int,
     *     total_pruned: int,
     *     remaining_records: int,
     *     status: string,
     *     message?: string
     * }
     */
    public function pruneTable(string $tableKey, bool $dryRun = false, ?int $chunkSize = null): array
    {
        $effectiveConfig = $this->getEffectiveConfig();

        /** @var array<string, array<string, mixed>> $allTables */
        $allTables = (array) ($effectiveConfig['tables'] ?? []);
        $config = null;

        if (isset($allTables[$tableKey])) {
            $config = $allTables[$tableKey];
        } else {
            foreach ($allTables as $cfg) {
                if (isset($cfg['table']) && $cfg['table'] === $tableKey) {
                    $config = $cfg;
                    break;
                }
            }
        }

        if ($config === null) {
            throw new InvalidArgumentException("Table '{$tableKey}' is not configured in pruning lifecycle rules.");
        }

        $tableName = (string) ($config['table'] ?? $tableKey);

        // Enforce Sovereign Exclusion Safeguard
        if ($this->isTableProtected($tableName)) {
            throw new InvalidArgumentException("Security Violation: Table '{$tableName}' is sovereign/protected and cannot be pruned.");
        }

        if (! Schema::hasTable($tableName)) {
            return [
                'table' => $tableName,
                'enabled' => (bool) ($config['enabled'] ?? false),
                'dry_run' => $dryRun,
                'date_pruned' => 0,
                'count_pruned' => 0,
                'total_pruned' => 0,
                'remaining_records' => 0,
                'status' => 'missing_table',
                'message' => "Database table '{$tableName}' does not exist.",
            ];
        }

        $primaryKey = (string) ($config['primary_key'] ?? 'id');
        $dateColumn = (string) ($config['date_column'] ?? 'created_at');
        $retentionDays = (int) ($config['retention_days'] ?? 0);
        $maxRecords = (int) ($config['max_records'] ?? 0);
        $batchSize = $chunkSize ?? (int) ($config['chunk_size'] ?? $effectiveConfig['chunk_size'] ?? 1000);
        $batchSize = max(1, $batchSize);

        $datePruned = 0;
        $countPruned = 0;

        // 1. Date-based Retention Pruning
        if ($retentionDays > 0) {
            $cutoff = now()->subDays($retentionDays);
            $dateQuery = DB::table($tableName)->where($dateColumn, '<', $cutoff);

            // Handle table-specific filters (e.g. only prune read notifications)
            if ($tableName === 'notifications' && ! empty($config['only_read'])) {
                $dateQuery->whereNotNull('read_at');
            }

            if ($dryRun) {
                $datePruned = (clone $dateQuery)->count();
            } else {
                do {
                    $ids = (clone $dateQuery)
                        ->limit($batchSize)
                        ->pluck($primaryKey)
                        ->all();

                    if (empty($ids)) {
                        break;
                    }

                    $deleted = DB::table($tableName)->whereIn($primaryKey, $ids)->delete();
                    $datePruned += $deleted;
                } while (count($ids) >= $batchSize);
            }
        }

        // 2. Count-based Maximum Capacity Pruning
        if ($maxRecords > 0) {
            if ($dryRun) {
                $currentTotal = DB::table($tableName)->count();
                $estimatedRemaining = max(0, $currentTotal - $datePruned);
                if ($estimatedRemaining > $maxRecords) {
                    $countPruned = $estimatedRemaining - $maxRecords;
                }
            } else {
                $currentTotal = DB::table($tableName)->count();
                if ($currentTotal > $maxRecords) {
                    $excess = $currentTotal - $maxRecords;
                    $remainingToPrune = $excess;

                    while ($remainingToPrune > 0) {
                        $limit = min($remainingToPrune, $batchSize);
                        $ids = DB::table($tableName)
                            ->orderBy($dateColumn, 'asc')
                            ->orderBy($primaryKey, 'asc')
                            ->limit($limit)
                            ->pluck($primaryKey)
                            ->all();

                        if (empty($ids)) {
                            break;
                        }

                        $deleted = DB::table($tableName)->whereIn($primaryKey, $ids)->delete();
                        $countPruned += $deleted;
                        $remainingToPrune -= $deleted;

                        if ($deleted === 0) {
                            break;
                        }
                    }
                }
            }
        }

        $totalPruned = $datePruned + $countPruned;

        // 3. Audit Trail Integration (Record only on actual execution with deleted records)
        if (! $dryRun && $totalPruned > 0) {
            $this->logPruningActivity($tableName, $datePruned, $countPruned, $totalPruned, $config);
        }

        $currentDbCount = DB::table($tableName)->count();
        $remainingRecords = $dryRun ? max(0, $currentDbCount - $totalPruned) : $currentDbCount;

        return [
            'table' => $tableName,
            'enabled' => true,
            'dry_run' => $dryRun,
            'date_pruned' => $datePruned,
            'count_pruned' => $countPruned,
            'total_pruned' => $totalPruned,
            'remaining_records' => $remainingRecords,
            'status' => 'success',
        ];
    }

    /**
     * Record automated pruning action in the Spatie activity log.
     *
     * @param  array<string, mixed>  $config
     */
    protected function logPruningActivity(
        string $table,
        int $datePruned,
        int $countPruned,
        int $totalPruned,
        array $config
    ): void {
        try {
            activity('data_pruning')
                ->withProperties([
                    'table' => $table,
                    'date_pruned' => $datePruned,
                    'count_pruned' => $countPruned,
                    'total_pruned' => $totalPruned,
                    'retention_days' => $config['retention_days'] ?? null,
                    'max_records' => $config['max_records'] ?? null,
                ])
                ->log("Auto-pruned {$totalPruned} records from table '{$table}' (Date: {$datePruned}, Capacity: {$countPruned})");
        } catch (\Throwable $e) {
            Log::warning("Failed to record pruning activity log for table '{$table}': {$e->getMessage()}");
        }
    }
}
