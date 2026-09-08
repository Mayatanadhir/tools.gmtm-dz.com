<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Data Pruning Master Switch
    |--------------------------------------------------------------------------
    | When set to false, all automated and manual pruning operations will be
    | halted globally across the entire application.
    */
    'enabled' => (bool) env('DATA_PRUNING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Batch Deletion Chunk Size
    |--------------------------------------------------------------------------
    | Deleting large record sets in chunks prevents excessive memory consumption,
    | replication lag, and prolonged table lock contention.
    */
    'chunk_size' => (int) env('DATA_PRUNING_CHUNK_SIZE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Sovereign & Critical Tables Blacklist
    |--------------------------------------------------------------------------
    | These tables are strictly forbidden from automated pruning to prevent
    | catastrophic authentication loss, permission collapse, or queue starvation.
    */
    'protected_tables' => [
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Target Tables Pruning Configurations
    |--------------------------------------------------------------------------
    | Define lifecycle policies for each operational log table.
    | - enabled: Toggle pruning for this specific table.
    | - table: Database table name.
    | - primary_key: Column used as unique identifier (e.g., 'id').
    | - date_column: Timestamp column used for retention filtering.
    | - retention_days: Keep records newer than N days (0 to disable).
    | - max_records: Maximum records capacity (0 to disable count pruning).
    */
    'tables' => [
        'activity_log' => [
            'enabled' => (bool) env('PRUNE_ACTIVITY_LOG_ENABLED', true),
            'table' => 'activity_log',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => (int) env('PRUNE_ACTIVITY_LOG_DAYS', 90),
            'max_records' => (int) env('PRUNE_ACTIVITY_LOG_MAX', 100000),
        ],

        'notifications' => [
            'enabled' => (bool) env('PRUNE_NOTIFICATIONS_ENABLED', true),
            'table' => 'notifications',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => (int) env('PRUNE_NOTIFICATIONS_DAYS', 60),
            'max_records' => (int) env('PRUNE_NOTIFICATIONS_MAX', 50000),
            'only_read' => (bool) env('PRUNE_NOTIFICATIONS_ONLY_READ', false),
        ],

        'failed_jobs' => [
            'enabled' => (bool) env('PRUNE_FAILED_JOBS_ENABLED', true),
            'table' => 'failed_jobs',
            'primary_key' => 'id',
            'date_column' => 'failed_at',
            'retention_days' => (int) env('PRUNE_FAILED_JOBS_DAYS', 30),
            'max_records' => (int) env('PRUNE_FAILED_JOBS_MAX', 10000),
        ],
    ],
];
