<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\MediaOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_it_compresses_and_converts_oversized_images_to_webp_under_max_width(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        // Create a 2400x1600 test image (exceeds 1920px max width threshold)
        $file = UploadedFile::fake()->image('hero_banner.jpg', 2400, 1600);

        $storedPath = $service->optimizeImage($file, 'photos', 'public');

        // Verify output file name and format
        $this->assertStringEndsWith('.webp', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists($storedPath));

        // Read the stored optimized artifact
        $contents = Storage::disk('public')->get($storedPath);
        $this->assertNotEmpty($contents);

        $manager = $service->getImageManager();
        $optimizedImage = $manager->decode($contents);

        // Verify dimensions were downscaled to max 1920px while maintaining aspect ratio
        $this->assertEquals(1920, $optimizedImage->width());
        $this->assertEquals(1280, $optimizedImage->height());
    }

    public function test_it_does_not_upscale_smaller_images(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        // Create a 600x400 image (under 1920px)
        $file = UploadedFile::fake()->image('thumbnail.png', 600, 400);

        $storedPath = $service->optimizeImage($file, 'photos', 'public');

        $contents = Storage::disk('public')->get($storedPath);
        $optimizedImage = $service->getImageManager()->decode($contents);

        $this->assertEquals(600, $optimizedImage->width());
        $this->assertEquals(400, $optimizedImage->height());
    }

    public function test_kill_step_permanently_destroys_temporary_scratch_files(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        $tempDir = config('media.temp_directory', storage_path('app/temp-media'));
        File::ensureDirectoryExists($tempDir);

        $scratchFile = $tempDir.'/scratch_test_'.uniqid().'.tmp';
        file_put_contents($scratchFile, 'temporary raw payload');

        $this->assertFileExists($scratchFile);

        $service->destroyTempFiles([$scratchFile]);

        $this->assertFileDoesNotExist($scratchFile);
    }

    public function test_it_stores_pdf_documents_using_optimization_or_resilient_fallback(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n149\n%%EOF";

        $tempDir = config('media.temp_directory', storage_path('app/temp-media'));
        File::ensureDirectoryExists($tempDir);
        $tempPdf = $tempDir.'/doc_'.uniqid().'.pdf';
        file_put_contents($tempPdf, $pdfContent);

        $storedPath = $service->optimizePdf($tempPdf, 'documents', 'public');

        $this->assertStringEndsWith('.pdf', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists($storedPath));
    }

    public function test_smart_router_dispatches_images_and_pdfs_appropriately(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        // Test image routing
        $imageFile = UploadedFile::fake()->image('test.jpg', 800, 600);
        $storedImagePath = $service->optimize($imageFile, 'media', 'public');
        $this->assertStringEndsWith('.webp', $storedImagePath);

        // Test PDF routing
        $pdfFile = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');
        $storedPdfPath = $service->optimize($pdfFile, 'media', 'public');
        $this->assertStringEndsWith('.pdf', $storedPdfPath);
    }

    public function test_profile_photo_upload_is_automatically_processed_under_webp_protocol(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $photo = UploadedFile::fake()->image('avatar_raw.png', 2048, 2048);

        $response = $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'photo' => $photo,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringEndsWith('.webp', $user->profile_photo_path);
        $this->assertTrue(Storage::disk('public')->exists($user->profile_photo_path));

        // Verify stored photo dimensions are clamped to the avatar spec (550×550 square)
        $contents = Storage::disk('public')->get($user->profile_photo_path);
        $optimizedImage = app(MediaOptimizationService::class)->getImageManager()->decode($contents);

        $this->assertEquals(550, $optimizedImage->width());
        $this->assertEquals(550, $optimizedImage->height());
    }

    public function test_it_deduplicates_identical_images_and_reuses_single_physical_file_on_disk(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        // Upload first image
        $file1 = UploadedFile::fake()->image('same_photo.png', 500, 500);
        $storedPath1 = $service->optimizeImage($file1, 'photos', 'public');

        // Upload second identical image (simulated identical raw bytes)
        $file2 = UploadedFile::fake()->image('same_photo.png', 500, 500);
        $storedPath2 = $service->optimizeImage($file2, 'photos', 'public');

        // Both uploads must resolve to the exact same content-addressed path
        $this->assertSame($storedPath1, $storedPath2);

        // Count physical files in photos directory on public disk
        $files = Storage::disk('public')->files('photos');
        $this->assertCount(1, $files);
    }

    public function test_it_preserves_shared_image_on_disk_when_one_user_removes_it_but_others_still_use_it(): void
    {
        /** @var MediaOptimizationService $service */
        $service = app(MediaOptimizationService::class);

        // Simulate shared image path (e.g. uploaded by user1 and deduplicated by user2)
        $sharedPath = 'photos/shared_avatar.webp';
        Storage::disk('public')->put($sharedPath, 'fake_webp_binary_content');

        /** @var User $user1 */
        $user1 = User::factory()->create(['profile_photo_path' => $sharedPath]);
        /** @var User $user2 */
        $user2 = User::factory()->create(['profile_photo_path' => $sharedPath]);

        $this->assertTrue(Storage::disk('public')->exists($sharedPath));
        $this->assertTrue($service->isAssetInUse($sharedPath, $user1->id));

        // User 1 removes their avatar
        $deleted = $service->safeDelete($user1->profile_photo_path, 'public', $user1->id);
        $user1->update(['profile_photo_path' => null]);

        // Must NOT be deleted from disk because User 2 is still using it
        $this->assertFalse($deleted);
        $this->assertTrue(Storage::disk('public')->exists($sharedPath));

        // Now User 2 also removes their avatar (0 references remain)
        $this->assertFalse($service->isAssetInUse($sharedPath, $user2->id));
        $deletedSecond = $service->safeDelete($user2->profile_photo_path, 'public', $user2->id);
        $user2->update(['profile_photo_path' => null]);

        // Now it must be purged from disk!
        $this->assertTrue($deletedSecond);
        $this->assertFalse(Storage::disk('public')->exists($sharedPath));
    }

    public function test_it_deletes_image_on_account_deletion_when_no_other_user_shares_it(): void
    {
        $uniquePath = 'photos/unique_avatar_'.uniqid().'.webp';
        Storage::disk('public')->put($uniquePath, 'unique_binary');

        /** @var User $user */
        $user = User::factory()->create(['profile_photo_path' => $uniquePath]);

        $this->assertTrue(Storage::disk('public')->exists($uniquePath));

        // Deleting the user triggers UserObserver::deleted() which calls safeDelete()
        $user->delete();

        // The unique photo must be purged from storage
        $this->assertFalse(Storage::disk('public')->exists($uniquePath));
    }
}
