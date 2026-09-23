---
paths:
  - 'app/Services/**,config/media.php,app/Http/Controllers/**,app/Observers/**'
---

# Media Processing & Zero Disk Space Leak Rules

## Mandatory Centralized Gateway (`MediaOptimizationService`)
- Direct, uncompressed file storage via raw Laravel methods (`$request->file('...')->store('...')`, `Storage::putFile(...)`) is strictly prohibited.
- All media uploads (images, PDFs, documents, avatars) MUST pass through `app(MediaOptimizationService::class)` (`optimizeImage()`, `optimizePdf()`, or `optimize()`).

## Compulsory WebP & Downscaling Standard
- All uploaded images must be converted to WebP format at 80% quality, width downscaled capped at 1920px (`scaleDown(width: 1920)`), and all EXIF metadata stripped. Upscaling is prohibited.

## Ghostscript PDF Optimization
- Process PDFs via Ghostscript (`gs`) using the `/ebook` profile (150dpi, compatibility 1.4) with resilient fallback to original.

## Mandatory Content-Addressable Storage (CAS) Deduplication
- Store media using cryptographic SHA-256 content hashes (`{sha256}.webp` / `{sha256}.pdf`).
- If an identical file already exists on disk, reuse its path immediately with zero redundant disk writes.

## Mandatory Reference-Aware Safe Deletion
- Unconditional deletion via `Storage::delete($path)` is strictly prohibited for shared media.
- Before unlinking, always check references via `MediaOptimizationService::isAssetInUse($path, $excludeUserId)` or call `MediaOptimizationService::safeDelete($path, $disk, $excludeUserId)`.
- If active references exist (> 0), the physical file is preserved on disk. It is permanently unlinked only when references reach zero.

## Process & Destroy Protocol
- Intermediate scratch files in `storage/app/temp-media` or system temp must be immediately purged via `@unlink()` (`destroyTempFiles()`). Zero temporary or raw files may linger on disk.
