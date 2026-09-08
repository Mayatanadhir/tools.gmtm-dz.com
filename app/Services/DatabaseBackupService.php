<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Resolve the Spatie BackupDestination instance.
     */
    public function getDestination(string $disk = 'local', ?string $backupName = null): BackupDestination
    {
        $name = $backupName ?? (string) config('backup.backup.name', config('app.name', 'Laravel'));

        return BackupDestination::create($disk, $name);
    }

    /**
     * Retrieve all available database backups with metadata and Oldest/Newest snapshot tags.
     *
     * @return array{
     *     backups: array<int, array{
     *         file_name: string,
     *         path: string,
     *         disk: string,
     *         size_bytes: int,
     *         size_formatted: string,
     *         date: Carbon,
     *         age: string,
     *         is_oldest: bool,
     *         is_newest: bool
     *     }>,
     *     total_count: int,
     *     total_size_bytes: int,
     *     total_size_formatted: string,
     *     oldest_backup: array<string, mixed>|null,
     *     newest_backup: array<string, mixed>|null
     * }
     */
    public function getBackups(string $disk = 'local'): array
    {
        $destination = $this->getDestination($disk);
        $backupCollection = $destination->backups();

        $items = [];
        $totalBytes = 0;

        foreach ($backupCollection as $backup) {
            /** @var Backup $backup */
            if (! $backup->exists()) {
                continue;
            }

            $size = (int) $backup->sizeInBytes();
            $totalBytes += $size;
            $date = Carbon::instance($backup->date());
            $path = (string) $backup->path();
            $fileName = basename($path);

            $items[] = [
                'file_name' => $fileName,
                'path' => $path,
                'disk' => $disk,
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'date' => $date,
                'age' => $date->diffForHumans(),
                'is_oldest' => false,
                'is_newest' => false,
            ];
        }

        // Sort descending by date (newest first)
        usort($items, fn (array $a, array $b): int => $b['date']->timestamp <=> $a['date']->timestamp);

        $oldestItem = null;
        $newestItem = null;

        if (! empty($items)) {
            // Newest is the first element
            $items[0]['is_newest'] = true;
            $newestItem = $items[0];

            // Oldest is the last element
            $lastIndex = count($items) - 1;
            $items[$lastIndex]['is_oldest'] = true;
            $oldestItem = $items[$lastIndex];
        }

        return [
            'backups' => $items,
            'total_count' => count($items),
            'total_size_bytes' => $totalBytes,
            'total_size_formatted' => $this->formatBytes($totalBytes),
            'oldest_backup' => $oldestItem,
            'newest_backup' => $newestItem,
        ];
    }

    /**
     * Create a new on-demand database backup.
     *
     * @return array{status: string, message: string}
     */
    public function createBackup(bool $onlyDb = true): array
    {
        try {
            $exitCode = Artisan::call('backup:run', [
                '--only-db' => $onlyDb,
                '--disable-notifications' => true,
            ]);

            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                Log::error("Backup creation failed with exit code {$exitCode}: {$output}");
                throw new RuntimeException("Failed to generate database backup: {$output}");
            }

            activity('database_backup')
                ->withProperties(['only_db' => $onlyDb, 'output' => $output])
                ->log('Created database backup snapshot');

            return [
                'status' => 'success',
                'message' => __('Database backup created successfully.'),
            ];
        } catch (\Throwable $e) {
            Log::error("Database backup creation exception: {$e->getMessage()}");
            throw new RuntimeException(__('Failed to create database backup: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Sanitize a backup file name and protect against directory traversal attacks.
     */
    public function sanitizeFileName(string $fileName): string
    {
        $trimmed = trim($fileName);

        if (str_contains($trimmed, '/') || str_contains($trimmed, '\\') || str_contains($trimmed, '..')) {
            throw new InvalidArgumentException("Directory traversal detected in backup file name: '{$fileName}'.");
        }

        $clean = basename($trimmed);

        if (! str_ends_with(strtolower($clean), '.zip') || ! preg_match('/^[a-zA-Z0-9_\-\.]+$/', $clean)) {
            throw new InvalidArgumentException("Invalid backup file name: '{$fileName}'.");
        }

        return $clean;
    }

    /**
     * Resolve the full relative path for a given backup filename on disk.
     */
    public function resolveBackupRelativePath(string $fileName, string $disk = 'local'): string
    {
        $clean = $this->sanitizeFileName($fileName);
        $name = (string) config('backup.backup.name', config('app.name', 'Laravel'));

        $path = "{$name}/{$clean}";

        if (! Storage::disk($disk)->exists($path)) {
            // Also check root of disk if stored without subfolder
            if (Storage::disk($disk)->exists($clean)) {
                return $clean;
            }

            throw new InvalidArgumentException("Backup file '{$clean}' does not exist on disk '{$disk}'.");
        }

        return $path;
    }

    /**
     * Delete a database backup snapshot from disk.
     */
    public function deleteBackup(string $fileName, string $disk = 'local'): void
    {
        $relativePath = $this->resolveBackupRelativePath($fileName, $disk);

        Storage::disk($disk)->delete($relativePath);

        activity('database_backup')
            ->withProperties(['file_name' => $fileName, 'path' => $relativePath, 'disk' => $disk])
            ->log("Deleted backup snapshot '{$fileName}'");
    }

    /**
     * Restore database from a specific backup zip snapshot.
     *
     * @return array{status: string, message: string, sql_file: string}
     */
    public function restoreBackup(string $fileName, string $disk = 'local'): array
    {
        $relativePath = $this->resolveBackupRelativePath($fileName, $disk);
        $fullPath = Storage::disk($disk)->path($relativePath);

        if (! file_exists($fullPath) || ! is_readable($fullPath)) {
            throw new RuntimeException("Backup file cannot be read at path: {$fullPath}");
        }

        $zip = new ZipArchive;
        $openResult = $zip->open($fullPath);

        if ($openResult !== true) {
            throw new RuntimeException("Failed to open backup zip archive (Error code: {$openResult}).");
        }

        // Find SQL dump file in the zip
        $sqlEntryName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = (string) $zip->getNameIndex($i);
            if (str_ends_with(strtolower($entry), '.sql') || str_ends_with(strtolower($entry), '.sqlite')) {
                $sqlEntryName = $entry;
                break;
            }
        }

        if ($sqlEntryName === null) {
            $zip->close();
            throw new RuntimeException("No valid database SQL dump found inside backup archive '{$fileName}'.");
        }

        $tempDir = storage_path('app/backup-temp/restore_'.uniqid('', true));
        File::ensureDirectoryExists($tempDir);

        try {
            $zip->extractTo($tempDir, [$sqlEntryName]);
            $zip->close();

            $extractedSqlPath = $tempDir.DIRECTORY_SEPARATOR.$sqlEntryName;

            if (! file_exists($extractedSqlPath)) {
                throw new RuntimeException("Extracted SQL dump was not found at '{$extractedSqlPath}'.");
            }

            $sqlContent = (string) file_get_contents($extractedSqlPath);

            if (empty(trim($sqlContent))) {
                throw new RuntimeException('The extracted SQL dump file is empty.');
            }

            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                try {
                    DB::unprepared($sqlContent);
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } else {
                DB::unprepared($sqlContent);
            }

            activity('database_backup')
                ->withProperties([
                    'file_name' => $fileName,
                    'sql_file' => $sqlEntryName,
                    'disk' => $disk,
                ])
                ->log("Restored database state from snapshot '{$fileName}'");

            return [
                'status' => 'success',
                'message' => __('Database successfully restored from snapshot :snapshot.', ['snapshot' => $fileName]),
                'sql_file' => $sqlEntryName,
            ];
        } catch (\Throwable $e) {
            Log::error("Database restoration failure for snapshot '{$fileName}': {$e->getMessage()}");
            throw new RuntimeException(__('Database restoration failed: :error', ['error' => $e->getMessage()]));
        } finally {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * Restore database from the oldest available snapshot.
     *
     * @return array{status: string, message: string, sql_file: string}
     */
    public function restoreOldestBackup(string $disk = 'local'): array
    {
        $data = $this->getBackups($disk);

        if (empty($data['oldest_backup'])) {
            throw new RuntimeException(__('No available database backups found to restore.'));
        }

        $oldestFileName = (string) $data['oldest_backup']['file_name'];

        return $this->restoreBackup($oldestFileName, $disk);
    }

    /**
     * Format bytes into human-readable string.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min((int) $pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
