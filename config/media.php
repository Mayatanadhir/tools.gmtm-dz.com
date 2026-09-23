<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Image Processing & Compression
    |--------------------------------------------------------------------------
    |
    | Configuration for Intervention Image processing.
    | Enforces Imagick driver by default with resilient fallback to GD
    | in environments where the Imagick C-extension is not installed.
    |
    */
    'image' => [
        // Driver: 'imagick' or 'gd'
        'driver' => env('MEDIA_IMAGE_DRIVER', extension_loaded('imagick') ? 'imagick' : 'gd'),

        // Gracefully fall back to GD if Imagick driver is configured but extension is missing
        'fallback_to_gd' => (bool) env('MEDIA_IMAGE_FALLBACK_TO_GD', true),

        // Max pixel width for uploaded images (Full HD standard: 1920px)
        'max_width' => (int) env('MEDIA_IMAGE_MAX_WIDTH', 1920),

        // Standard WebP output compression quality (0-100)
        'quality' => (int) env('MEDIA_IMAGE_QUALITY', 80),

        // Standardized format: compulsory WebP conversion
        'format' => env('MEDIA_IMAGE_FORMAT', 'webp'),

        // Strip camera, GPS, and EXIF metadata for privacy and size reduction
        'strip_metadata' => (bool) env('MEDIA_IMAGE_STRIP_METADATA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar / Profile Photo Processing
    |--------------------------------------------------------------------------
    |
    | Standardized dimension thresholds for user profile pictures (avatars).
    | Enforces a unified 550x550 pixel default dimension limit.
    |
    */
    'avatar' => [
        // Default max pixel width for profile photos (default: 550px)
        'max_width' => (int) env('MEDIA_AVATAR_MAX_WIDTH', 550),

        // Default max pixel height for profile photos (default: 550px)
        'max_height' => (int) env('MEDIA_AVATAR_MAX_HEIGHT', 550),

        // Unify to 1:1 square aspect ratio via smart center-crop
        'crop_square' => (bool) env('MEDIA_AVATAR_CROP_SQUARE', true),

        // Whether to upscale smaller images to exact dimensions
        'upscale' => (bool) env('MEDIA_AVATAR_UPSCALE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document (PDF) Processing & Compression
    |--------------------------------------------------------------------------
    |
    | Configuration for Ghostscript (gs) PDF compression via Symfony Process.
    | Applies /ebook profile (150dpi) reducing payload size up to 80%.
    |
    */
    'pdf' => [
        // Path to the Ghostscript binary executable
        'binary' => env('MEDIA_GHOSTSCRIPT_PATH', 'gs'),

        // Ghostscript PDF settings profile (/screen, /ebook, /printer, /prepress)
        'pdf_settings' => env('MEDIA_PDF_SETTINGS', '/ebook'),

        // PDF compatibility level (e.g. 1.4)
        'compatibility_level' => env('MEDIA_PDF_COMPATIBILITY_LEVEL', '1.4'),

        // Maximum process execution timeout in seconds
        'timeout' => (int) env('MEDIA_PDF_TIMEOUT', 60),

        // If Ghostscript is not installed or fails, store original without crashing
        'fallback_to_original' => (bool) env('MEDIA_PDF_FALLBACK_TO_ORIGINAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage & Temporary Directory
    |--------------------------------------------------------------------------
    |
    | Intermediate storage and scratch directory for the Process & Destroy protocol.
    | Temporary files in this directory are destroyed immediately after processing.
    |
    */
    'temp_directory' => env('MEDIA_TEMP_DIR', storage_path('app/temp-media')),

    // Default public disk for optimized media assets
    'default_disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Deduplication & Content-Addressable Storage (CAS)
    |--------------------------------------------------------------------------
    |
    | Enables content-based deduplication using cryptographic SHA-256 hashes.
    | When enabled, identical uploaded images/documents share a single physical
    | storage file, preventing redundant disk usage.
    |
    */
    'deduplication' => [
        'enabled' => (bool) env('MEDIA_DEDUPLICATION_ENABLED', true),
        'hash_filenames' => (bool) env('MEDIA_HASH_FILENAMES', true),
    ],

];
