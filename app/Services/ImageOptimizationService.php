<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class ImageOptimizationService extends BaseService
{
    protected ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        $this->manager = $manager ?? new ImageManager(new Driver);
    }

    /**
     * Determine maximum width constraint based on filename conventions or explicit override.
     */
    public function calculateMaxWidth(string $filename, ?int $explicitMaxWidth = null): int
    {
        if ($explicitMaxWidth !== null && $explicitMaxWidth > 0) {
            return $explicitMaxWidth;
        }

        if (str_starts_with($filename, 'client-')) {
            return 600;
        }

        if (str_starts_with($filename, 'MAYATA')) {
            return 800;
        }

        return 1400;
    }

    /**
     * Optimize and compress a single image file safely.
     *
     * @param  array{
     *     max_width?: int|null,
     *     quality?: int,
     *     backup?: bool
     * }  $options
     * @return array{
     *     filename: string,
     *     path: string,
     *     original_size: int,
     *     new_size: int,
     *     saved_bytes: int,
     *     saved_percent: float,
     *     status: 'success'|'skipped'|'error',
     *     error: string|null
     * }
     */
    public function optimizeImage(string $filePath, array $options = []): array
    {
        $filename = basename($filePath);

        if (! File::exists($filePath)) {
            return [
                'filename' => $filename,
                'path' => $filePath,
                'original_size' => 0,
                'new_size' => 0,
                'saved_bytes' => 0,
                'saved_percent' => 0.0,
                'status' => 'error',
                'error' => 'File does not exist',
            ];
        }

        $originalSize = (int) File::size($filePath);

        try {
            // Optional non-destructive backup
            if (! empty($options['backup'])) {
                $backupPath = $filePath.'.bak';
                if (! File::exists($backupPath)) {
                    File::copy($filePath, $backupPath);
                }
            }

            $image = method_exists($this->manager, 'decodePath')
                ? $this->manager->decodePath($filePath)
                : (method_exists($this->manager, 'read')
                    ? $this->manager->read($filePath)
                    : $this->manager->decode($filePath));

            // Protection against corrupted image or zero dimensions
            if ($image->width() <= 0 || $image->height() <= 0) {
                return [
                    'filename' => $filename,
                    'path' => $filePath,
                    'original_size' => $originalSize,
                    'new_size' => $originalSize,
                    'saved_bytes' => 0,
                    'saved_percent' => 0.0,
                    'status' => 'error',
                    'error' => 'Invalid image dimensions (width or height <= 0)',
                ];
            }

            $maxWidth = $this->calculateMaxWidth($filename, $options['max_width'] ?? null);

            // Scale down while maintaining aspect ratio and respecting alpha channel
            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $quality = $options['quality'] ?? 82;
            $image->save($filePath, quality: $quality);

            clearstatcache(true, $filePath);
            $newSize = (int) File::size($filePath);
            $savedBytes = max(0, $originalSize - $newSize);
            $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 1) : 0.0;

            return [
                'filename' => $filename,
                'path' => $filePath,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'saved_bytes' => $savedBytes,
                'saved_percent' => $savedPercent,
                'status' => 'success',
                'error' => null,
            ];
        } catch (Throwable $e) {
            Log::error(sprintf('[ImageOptimizationService] Failed to optimize %s: %s', $filePath, $e->getMessage()), [
                'exception' => $e,
            ]);

            return [
                'filename' => $filename,
                'path' => $filePath,
                'original_size' => $originalSize,
                'new_size' => $originalSize,
                'saved_bytes' => 0,
                'saved_percent' => 0.0,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize all valid images inside a directory or specific list of files.
     *
     * @param  array<int, string>  $specificFiles
     * @param  array{
     *     max_width?: int|null,
     *     quality?: int,
     *     backup?: bool
     * }  $options
     * @return array<int, array{
     *     filename: string,
     *     path: string,
     *     original_size: int,
     *     new_size: int,
     *     saved_bytes: int,
     *     saved_percent: float,
     *     status: 'success'|'skipped'|'error',
     *     error: string|null
     * }>
     */
    public function optimizeDirectory(string $directory, array $specificFiles = [], array $options = []): array
    {
        $results = [];

        if (! File::isDirectory($directory)) {
            return $results;
        }

        if (! empty($specificFiles)) {
            foreach ($specificFiles as $filename) {
                $filePath = $directory.DIRECTORY_SEPARATOR.$filename;
                if (File::exists($filePath)) {
                    $results[] = $this->optimizeImage($filePath, $options);
                }
            }

            return $results;
        }

        $files = File::files($directory);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $allowedExtensions, true)) {
                $results[] = $this->optimizeImage($file->getPathname(), $options);
            }
        }

        return $results;
    }
}
