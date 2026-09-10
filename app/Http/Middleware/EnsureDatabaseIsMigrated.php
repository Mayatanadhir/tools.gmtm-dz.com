<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PermissionDiscoveryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureDatabaseIsMigrated
{
    /**
     * Runtime memoization to avoid redundant checks during the same request lifecycle.
     */
    protected static array $runtimeVerified = [];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $defaultConn = (string) config('database.default');
        $dbName = (string) config("database.connections.{$defaultConn}.database");

        if (! isset(static::$runtimeVerified[$dbName])) {
            $ready = $this->ensureSchemaReady($defaultConn, $dbName);

            if (! $ready) {
                return response()->view('errors.database', [
                    'connection' => $defaultConn,
                    'database' => $dbName,
                    'host' => (string) config("database.connections.{$defaultConn}.host", '127.0.0.1'),
                    'port' => (string) config("database.connections.{$defaultConn}.port", '3306'),
                ], 503);
            }

            static::$runtimeVerified[$dbName] = true;
        }

        return $next($request);
    }

    /**
     * Verify database schema and run migrations automatically if missing.
     */
    protected function ensureSchemaReady(string $connection, string $dbName): bool
    {
        try {
            $cacheKey = "system_schema_migrated_{$dbName}";

            if (Cache::get($cacheKey, false)) {
                return true;
            }

            // Step 1: Check database connectivity and auto-create if missing
            $connected = $this->ensureDatabaseExists($connection, $dbName);
            if (! $connected) {
                return false;
            }

            // Step 2: Check for pending migrations
            $migrator = app('migrator');
            $paths = array_merge([database_path('migrations')], $migrator->paths());
            $files = $migrator->getMigrationFiles($paths);

            $needsMigration = false;

            if (! $migrator->repositoryExists() || ! Schema::connection($connection)->hasTable('migrations')) {
                $needsMigration = true;
            } else {
                $ran = $migrator->getRepository()->getRan();
                $pending = array_diff(array_keys($files), $ran);
                if (count($pending) > 0) {
                    $needsMigration = true;
                }
            }

            if (! $needsMigration) {
                $needsMigration = ! Schema::connection($connection)->hasTable('users')
                    || ! Schema::connection($connection)->hasTable('system_settings');
            }

            // Step 3: Run migrations and seed baseline state
            if ($needsMigration) {
                Artisan::call('migrate', ['--force' => true]);

                if (Schema::connection($connection)->hasTable('roles') && Role::count() === 0) {
                    try {
                        Artisan::call('db:seed', ['--force' => true]);
                    } catch (Throwable) {
                        // If seeding encounters duplicate constraints, continue safely
                    }
                }

                try {
                    /** @var PermissionDiscoveryService $discoveryService */
                    $discoveryService = app(PermissionDiscoveryService::class);
                    $discoveryService->generateCrudPermissionsForTables();
                } catch (Throwable) {
                    // Continue safely
                }
            }

            Cache::put($cacheKey, true, 3600);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Ensure database exists, automatically provisioning it if absent.
     */
    protected function ensureDatabaseExists(string $connection, string $dbName): bool
    {
        try {
            DB::connection($connection)->getPdo();

            return true;
        } catch (PDOException $e) {
            // Check if error is specifically "Unknown database"
            if ($this->isDatabaseMissingError($e)) {
                $created = $this->autoCreateDatabase($connection, $dbName);
                if ($created) {
                    try {
                        DB::purge($connection);
                        DB::reconnect($connection);
                        DB::connection($connection)->getPdo();

                        return true;
                    } catch (Throwable) {
                        return false;
                    }
                }
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Determine if a PDO exception is caused by a missing database.
     */
    protected function isDatabaseMissingError(PDOException $e): bool
    {
        $message = strtolower($e->getMessage());

        return $e->getCode() === 1049
            || str_contains($message, 'unknown database')
            || str_contains($message, 'does not exist')
            || $e->getCode() === '3D000';
    }

    /**
     * Automatically create database using driver-specific provisioning.
     */
    protected function autoCreateDatabase(string $connection, string $dbName): bool
    {
        /** @var array<string, mixed> $config */
        $config = config("database.connections.{$connection}", []);
        $driver = (string) ($config['driver'] ?? 'mysql');

        return match ($driver) {
            'mysql', 'mariadb' => $this->autoCreateMysqlDatabase($dbName, $config),
            'sqlite' => $this->autoCreateSqliteDatabase($dbName),
            'pgsql' => $this->autoCreatePgsqlDatabase($dbName, $config),
            default => false,
        };
    }

    /**
     * Auto-create MySQL/MariaDB database.
     *
     * @param  array<string, mixed>  $config
     */
    protected function autoCreateMysqlDatabase(string $dbName, array $config): bool
    {
        try {
            $host = (string) ($config['host'] ?? '127.0.0.1');
            $port = (string) ($config['port'] ?? '3306');
            $user = (string) ($config['username'] ?? 'root');
            $pass = (string) ($config['password'] ?? '');
            $charset = (string) ($config['charset'] ?? 'utf8mb4');
            $collation = (string) ($config['collation'] ?? 'utf8mb4_unicode_ci');

            $dsn = "mysql:host={$host};port={$port};charset={$charset}";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $escapedDb = str_replace('`', '``', $dbName);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$escapedDb}` CHARACTER SET {$charset} COLLATE {$collation}");

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Auto-create SQLite database file.
     */
    protected function autoCreateSqliteDatabase(string $dbPath): bool
    {
        try {
            if ($dbPath === ':memory:') {
                return true;
            }

            if (! file_exists($dbPath)) {
                $dir = dirname($dbPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                touch($dbPath);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Auto-create PostgreSQL database.
     *
     * @param  array<string, mixed>  $config
     */
    protected function autoCreatePgsqlDatabase(string $dbName, array $config): bool
    {
        try {
            $host = (string) ($config['host'] ?? '127.0.0.1');
            $port = (string) ($config['port'] ?? '5432');
            $user = (string) ($config['username'] ?? 'postgres');
            $pass = (string) ($config['password'] ?? '');

            $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $escapedDb = str_replace('"', '""', $dbName);
            $pdo->exec("CREATE DATABASE \"{$escapedDb}\"");

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
