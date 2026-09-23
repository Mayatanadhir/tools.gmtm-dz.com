<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageManagerInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * ADR-009: Media Optimization & Zero Disk Space Leak Engine.
 *
 * Centralized gateway enforcing compulsory WebP conversion (80% quality, max 1920px),
 * Ghostscript PDF compression (/ebook 150dpi), and the "Process & Destroy" protocol
 * ensuring zero uncompressed or temporary files linger on the host storage.
 */
class MediaOptimizationService
{
    private ?ImageManagerInterface $imageManager = null;

    /**
     * Resolve the Intervention Image manager instance based on configuration and host capabilities.
     */
    public function getImageManager(): ImageManagerInterface
    {
        if ($this->imageManager !== null) {
            return $this->imageManager;
        }

        $driver = config('media.image.driver', 'imagick');
        $fallbackToGd = (bool) config('media.image.fallback_to_gd', true);

        if ($driver === 'imagick') {
            if (extension_loaded('imagick') && class_exists('Imagick')) {
                return $this->imageManager = new ImageManager(new ImagickDriver);
            }

            if ($fallbackToGd && extension_loaded('gd')) {
                Log::info('Imagick extension not detected on host. Gracefully falling back to GD driver.');

                return $this->imageManager = new ImageManager(new GdDriver);
            }

            throw new RuntimeException('Imagick PHP extension is required by configuration but not available on this environment.');
        }

        if ($driver === 'gd' && extension_loaded('gd')) {
            return $this->imageManager = new ImageManager(new GdDriver);
        }

        throw new RuntimeException("Configured image driver [{$driver}] is not available on this host.");
    }

    /**
     * Set a custom image manager instance (e.g. for testing or driver mocking).
     */
    public function setImageManager(ImageManagerInterface $manager): self
    {
        $this->imageManager = $manager;

        return $this;
    }

    /**
     * Process, resize, compress to WebP, and store an image under the Process & Destroy protocol.
     *
     * @param  UploadedFile|string  $file  Uploaded file instance or absolute filesystem path
     * @param  string  $directory  Target directory inside the destination disk (e.g. 'photos')
     * @param  string|null  $disk  Destination storage disk (defaults to config: 'public')
     * @return string The relative stored file path (e.g. 'photos/xyz.webp')
     */
    public function optimizeImage(UploadedFile|string $file, string $directory = 'photos', ?string $disk = null): string
    {
        $disk = $disk ?? config('media.default_disk', 'public');
        $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($sourcePath)) {
            throw new RuntimeException("Source image file [{$sourcePath}] does not exist.");
        }

        $maxWidth = (int) config('media.image.max_width', 1920);
        $quality = (int) config('media.image.quality', 80);
        $strip = (bool) config('media.image.strip_metadata', true);

        $manager = $this->getImageManager();
        $image = $manager->decode($sourcePath);

        // Scale down if pixel width exceeds the max threshold (maintains aspect ratio, never upscales)
        if ($maxWidth > 0 && $image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // Compulsory WebP conversion with stripped metadata
        $encoded = $image->encode(new WebpEncoder(quality: $quality, strip: $strip));
        $encodedContent = (string) $encoded;

        // Content-Addressable Storage (CAS) & Deduplication
        $deduplicationEnabled = (bool) config('media.deduplication.enabled', true);
        $hashFilenames = (bool) config('media.deduplication.hash_filenames', true);

        if ($deduplicationEnabled || $hashFilenames) {
            $hash = hash('sha256', $encodedContent);
            $filename = $hash.'.webp';
        } else {
            $filename = Str::random(40).'.webp';
        }

        $destinationPath = trim($directory, '/').'/'.$filename;

        // Deduplication check: if an identical file exists, reuse it without duplicating on disk
        if ($deduplicationEnabled && Storage::disk($disk)->exists($destinationPath)) {
            Log::info("Deduplication matched: asset [{$destinationPath}] already exists. Reusing existing file.");
            $this->destroyTempFiles([$sourcePath]);

            return $destinationPath;
        }

        // Store optimized artifact on target disk
        Storage::disk($disk)->put($destinationPath, $encodedContent);

        // The Kill-Step: Destroy temporary upload copy if it resides in system/scratch temp
        $this->destroyTempFiles([$sourcePath]);

        return $destinationPath;
    }

    /**
     * Process and store a user profile photo (avatar) under the Process & Destroy protocol.
     *
     * Enforces a unified square aspect ratio and a maximum of 550×550 px (configurable via
     * config/media.php `avatar` block). The image is center-cropped to a 1:1 square, scaled
     * down to the configured maximum dimension, then converted to WebP and stored on disk.
     *
     * @param  UploadedFile|string  $file  Uploaded file instance or absolute filesystem path
     * @param  string  $directory  Target directory inside the destination disk (default: 'photos')
     * @param  string|null  $disk  Destination storage disk (defaults to config: 'public')
     * @return string The relative stored file path (e.g. 'photos/xyz.webp')
     */
    public function optimizeAvatar(UploadedFile|string $file, string $directory = 'photos', ?string $disk = null): string
    {
        $disk = $disk ?? config('media.default_disk', 'public');
        $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($sourcePath)) {
            throw new RuntimeException("Source avatar file [{$sourcePath}] does not exist.");
        }

        $maxWidth = (int) config('media.avatar.max_width', 550);
        $maxHeight = (int) config('media.avatar.max_height', 550);
        $cropSquare = (bool) config('media.avatar.crop_square', true);
        $upscale = (bool) config('media.avatar.upscale', false);
        $quality = (int) config('media.image.quality', 80);
        $strip = (bool) config('media.image.strip_metadata', true);

        $manager = $this->getImageManager();
        $image = $manager->decode($sourcePath);

        if ($cropSquare) {
            // Center-crop to exact square dimensions — scales up if smaller than target
            $image->cover($maxWidth, $maxHeight);
        } else {
            // Preserve aspect ratio, only scale down (never upscale unless configured)
            if ($upscale) {
                $image->scaleDown($maxWidth, $maxHeight);
            } elseif ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            }
        }

        // Compulsory WebP conversion with stripped metadata
        $encoded = $image->encode(new WebpEncoder(quality: $quality, strip: $strip));
        $encodedContent = (string) $encoded;

        // Content-Addressable Storage (CAS) & Deduplication
        $deduplicationEnabled = (bool) config('media.deduplication.enabled', true);
        $hashFilenames = (bool) config('media.deduplication.hash_filenames', true);

        if ($deduplicationEnabled || $hashFilenames) {
            $hash = hash('sha256', $encodedContent);
            $filename = $hash.'.webp';
        } else {
            $filename = Str::random(40).'.webp';
        }

        $destinationPath = trim($directory, '/').'/'.$filename;

        // Deduplication check: if an identical file exists, reuse it without duplicating on disk
        if ($deduplicationEnabled && Storage::disk($disk)->exists($destinationPath)) {
            Log::info("Deduplication matched: avatar [{$destinationPath}] already exists. Reusing existing file.");
            $this->destroyTempFiles([$sourcePath]);

            return $destinationPath;
        }

        // Store optimised artifact on target disk
        Storage::disk($disk)->put($destinationPath, $encodedContent);

        // The Kill-Step: Destroy temporary upload copy
        $this->destroyTempFiles([$sourcePath]);

        return $destinationPath;
    }

    /**
     * Compress a PDF document using Ghostscript (/ebook 150dpi profile) under the Process & Destroy protocol.
     *
     * @param  UploadedFile|string  $file  Uploaded file instance or absolute filesystem path
     * @param  string  $directory  Target directory inside the destination disk (e.g. 'documents')
     * @param  string|null  $disk  Destination storage disk (defaults to config: 'public')
     * @return string The relative stored file path (e.g. 'documents/xyz.pdf')
     */
    public function optimizePdf(UploadedFile|string $file, string $directory = 'documents', ?string $disk = null): string
    {
        $disk = $disk ?? config('media.default_disk', 'public');
        $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($sourcePath)) {
            throw new RuntimeException("Source PDF file [{$sourcePath}] does not exist.");
        }

        $gsBinary = config('media.pdf.binary', 'gs');
        $isGsAvailable = $this->isGhostscriptAvailable($gsBinary);

        $deduplicationEnabled = (bool) config('media.deduplication.enabled', true);
        $hashFilenames = (bool) config('media.deduplication.hash_filenames', true);

        // If Ghostscript is not available on host, apply fallback or exception
        if (! $isGsAvailable) {
            if (config('media.pdf.fallback_to_original', true)) {
                Log::warning("Ghostscript binary [{$gsBinary}] is not available. Storing PDF uncompressed.");
                $rawContent = (string) file_get_contents($sourcePath);

                $filename = ($deduplicationEnabled || $hashFilenames)
                    ? hash('sha256', $rawContent).'.pdf'
                    : Str::random(40).'.pdf';
                $destinationPath = trim($directory, '/').'/'.$filename;

                if ($deduplicationEnabled && Storage::disk($disk)->exists($destinationPath)) {
                    Log::info("Deduplication matched: PDF [{$destinationPath}] already exists. Reusing existing file.");
                    $this->destroyTempFiles([$sourcePath]);

                    return $destinationPath;
                }

                Storage::disk($disk)->put($destinationPath, $rawContent);
                $this->destroyTempFiles([$sourcePath]);

                return $destinationPath;
            }

            throw new RuntimeException("Ghostscript binary [{$gsBinary}] is required for PDF optimization but not executable.");
        }

        // Prepare scratch directory and temporary paths
        $tempDir = config('media.temp_directory', storage_path('app/temp-media'));
        File::ensureDirectoryExists($tempDir);

        $tempInput = $tempDir.'/'.Str::random(24).'_in.pdf';
        $tempOutput = $tempDir.'/'.Str::random(24).'_out.pdf';

        try {
            File::copy($sourcePath, $tempInput);

            $compatibility = config('media.pdf.compatibility_level', '1.4');
            $pdfSettings = config('media.pdf.pdf_settings', '/ebook');
            $timeout = (int) config('media.pdf.timeout', 60);

            $process = new Process([
                $gsBinary,
                '-sDEVICE=pdfwrite',
                "-dCompatibilityLevel={$compatibility}",
                "-dPDFSETTINGS={$pdfSettings}",
                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',
                "-sOutputFile={$tempOutput}",
                $tempInput,
            ]);

            $process->setTimeout($timeout);
            $process->run();

            if ($process->isSuccessful() && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                $finalContent = (string) file_get_contents($tempOutput);
            } else {
                Log::warning('Ghostscript PDF compression failed or output was empty: '.$process->getErrorOutput());

                if (! config('media.pdf.fallback_to_original', true)) {
                    throw new RuntimeException('Ghostscript PDF compression failed: '.$process->getErrorOutput());
                }

                $finalContent = (string) file_get_contents($sourcePath);
            }

            $filename = ($deduplicationEnabled || $hashFilenames)
                ? hash('sha256', $finalContent).'.pdf'
                : Str::random(40).'.pdf';
            $destinationPath = trim($directory, '/').'/'.$filename;

            if ($deduplicationEnabled && Storage::disk($disk)->exists($destinationPath)) {
                Log::info("Deduplication matched: PDF [{$destinationPath}] already exists. Reusing existing file.");

                return $destinationPath;
            }

            Storage::disk($disk)->put($destinationPath, $finalContent);
        } finally {
            // The Kill-Step: Destroy all intermediate scratch files unconditionally
            $this->destroyTempFiles([$tempInput, $tempOutput, $sourcePath]);
        }

        return $destinationPath;
    }

    /**
     * Smart routing entry point inspecting MIME type to dispatch to image or PDF compression.
     */
    public function optimize(UploadedFile|string $file, string $directory = 'media', ?string $disk = null): string
    {
        $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $mime = mime_content_type($sourcePath) ?: '';

        if (str_starts_with($mime, 'image/')) {
            return $this->optimizeImage($file, $directory, $disk);
        }

        if ($mime === 'application/pdf') {
            return $this->optimizePdf($file, $directory, $disk);
        }

        // Generic safe storage for other allowed documents
        $disk = $disk ?? config('media.default_disk', 'public');
        $ext = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = Str::random(40).($ext ? '.'.$ext : '');
        $destinationPath = trim($directory, '/').'/'.$filename;

        Storage::disk($disk)->put($destinationPath, (string) file_get_contents($sourcePath));
        $this->destroyTempFiles([$sourcePath]);

        return $destinationPath;
    }

    /**
     * Check if Ghostscript binary is present and executable.
     */
    public function isGhostscriptAvailable(?string $binary = null): bool
    {
        $binary = $binary ?? config('media.pdf.binary', 'gs');

        try {
            $process = new Process([$binary, '-v']);
            $process->setTimeout(5);
            $process->run();

            return $process->isSuccessful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The Kill-Step: Permanently remove temporary files and intermediate scratch artifacts.
     *
     * @param  array<int, string|null>  $paths
     */
    public function destroyTempFiles(array $paths): void
    {
        $tempDir = realpath(config('media.temp_directory', storage_path('app/temp-media'))) ?: '';
        $sysTempDir = realpath(sys_get_temp_dir()) ?: '';

        foreach ($paths as $path) {
            if (blank($path) || ! file_exists($path)) {
                continue;
            }

            $realPath = realpath($path);
            if (! $realPath) {
                continue;
            }

            // Only delete files if they reside within a temp scratch location or are upload temp files
            $isInAppTemp = $tempDir !== '' && str_starts_with($realPath, $tempDir);
            $isInSysTemp = $sysTempDir !== '' && str_starts_with($realPath, $sysTempDir);
            $isPhpTemp = str_contains($realPath, 'php') || str_contains($realPath, 'tmp');

            if ($isInAppTemp || $isInSysTemp || $isPhpTemp) {
                @unlink($realPath);
            }
        }
    }

    /**
     * Check if a given media asset path is currently referenced by any active user or entity in the database.
     *
     * @param  string  $path  Relative path on disk (e.g. 'photos/abc.webp')
     * @param  int|string|null  $excludeUserId  User ID to exclude from reference check (e.g. user updating their avatar)
     * @return bool True if at least one other record references the asset, false otherwise
     */
    public function isAssetInUse(string $path, int|string|null $excludeUserId = null, ?string $modelClass = null, int|string|null $excludeModelId = null): bool
    {
        if (blank($path)) {
            return false;
        }

        try {
            $query = User::query()->where('profile_photo_path', $path);

            if ($excludeUserId !== null && ($modelClass === null || $modelClass === User::class)) {
                $query->where('id', '!=', $excludeUserId);
            }

            if ($query->exists()) {
                return true;
            }

            if (class_exists(Employee::class)) {
                $empQuery = Employee::query()->where('profile_photo_path', $path);

                if ($excludeModelId !== null || ($modelClass === Employee::class && $excludeUserId !== null)) {
                    $excludeId = $excludeModelId ?? $excludeUserId;
                    $empQuery->where('id', '!=', $excludeId);
                }

                if ($empQuery->exists()) {
                    return true;
                }
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Safely delete a media file from disk only if no other entity/user is currently referencing it.
     * Prevents accidental asset loss when deduplicated assets are shared across multiple records.
     *
     * @param  string|null  $path  Relative path on disk (e.g. 'photos/abc.webp')
     * @param  string|null  $disk  Storage disk (defaults to config: 'media.default_disk' or 'public')
     * @param  int|string|null  $excludeUserId  User ID to exclude (e.g. the user releasing their reference)
     * @return bool True if physical file was actually deleted; false if preserved or skipped
     */
    public function safeDelete(?string $path, ?string $disk = null, int|string|null $excludeUserId = null): bool
    {
        if (blank($path)) {
            return false;
        }

        $disk = $disk ?? config('media.default_disk', 'public');

        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        if ($this->isAssetInUse($path, $excludeUserId)) {
            Log::info("Safe Delete: Asset [{$path}] is preserved on disk because other record(s) still reference it.");

            return false;
        }

        Storage::disk($disk)->delete($path);
        Log::info("Safe Delete: Asset [{$path}] has 0 references remaining. Successfully purged from disk.");

        return true;
    }
}
