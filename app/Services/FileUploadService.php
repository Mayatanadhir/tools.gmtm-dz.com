<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class FileUploadService extends BaseService
{
    /**
     * Dangerous file extensions strictly disallowed by default.
     *
     * @var list<string>
     */
    protected const DISALLOWED_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin', 'cgi', 'pl', 'py', 'js', 'vbs',
    ];

    /**
     * Upload an uploaded file to the specified disk and directory with a unique UUID name.
     *
     * @param  list<string>  $allowedExtensions  Optional whitelist of allowed extensions (case-insensitive).
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public',
        array $allowedExtensions = []
    ): string {
        if (! $file->isValid()) {
            throw new InvalidArgumentException(sprintf('The uploaded file is not valid: %s', $file->getErrorMessage()));
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

        if ($extension === '') {
            throw new InvalidArgumentException('Could not determine file extension for uploaded file.');
        }

        if (in_array($extension, self::DISALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException(sprintf('File extension [%s] is dangerous and strictly forbidden.', $extension));
        }

        if (! empty($allowedExtensions)) {
            $normalizedAllowed = array_map('strtolower', $allowedExtensions);
            if (! in_array($extension, $normalizedAllowed, true)) {
                throw new InvalidArgumentException(sprintf(
                    'File extension [%s] is not permitted. Allowed extensions: %s',
                    $extension,
                    implode(', ', $normalizedAllowed)
                ));
            }
        }

        $uniqueFilename = sprintf('%s.%s', Str::uuid()->toString(), $extension);
        $cleanDirectory = trim($directory, '/\\');

        $storedPath = $file->storeAs($cleanDirectory, $uniqueFilename, ['disk' => $disk]);

        if ($storedPath === false) {
            throw new RuntimeException(sprintf('Failed to store file on disk [%s] in directory [%s].', $disk, $cleanDirectory));
        }

        return $storedPath;
    }

    /**
     * Safely delete a file from the specified storage disk using reference-aware check.
     */
    public function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return app(MediaOptimizationService::class)->safeDelete($path, $disk);
    }

    /**
     * Optimize and store an uploaded media asset (image or document) using MediaOptimizationService.
     */
    public function optimizeMedia(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public'
    ): string {
        return app(MediaOptimizationService::class)->optimize($file, $directory, $disk);
    }

    /**
     * Replace an existing file with a newly uploaded file.
     *
     * @param  list<string>  $allowedExtensions
     */
    public function replaceFile(
        UploadedFile $newFile,
        ?string $oldPath = null,
        string $directory = 'uploads',
        string $disk = 'public',
        array $allowedExtensions = []
    ): string {
        $newPath = $this->uploadFile($newFile, $directory, $disk, $allowedExtensions);

        if ($oldPath !== null && $oldPath !== '') {
            $this->deleteFile($oldPath, $disk);
        }

        return $newPath;
    }

    /**
     * Determine if a file exists on the specified storage disk.
     */
    public function fileExists(?string $path, string $disk = 'public'): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get the publicly accessible URL for a given file path.
     */
    public function getUrl(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /**
     * Get the file size in bytes.
     */
    public function getSize(string $path, string $disk = 'public'): int
    {
        return Storage::disk($disk)->size($path);
    }
}
