<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageOptimizationServiceTest extends TestCase
{
    private ImageOptimizationService $service;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ImageOptimizationService;
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test_images_'.uniqid();
        File::makeDirectory($this->tempDir, 0755, true, true);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    public function test_calculate_max_width_resolves_correct_dimensions(): void
    {
        $this->assertSame(600, $this->service->calculateMaxWidth('client-logo.jpg'));
        $this->assertSame(800, $this->service->calculateMaxWidth('MAYATA-Ahmed.png'));
        $this->assertSame(1400, $this->service->calculateMaxWidth('general-photo.jpg'));
        $this->assertSame(500, $this->service->calculateMaxWidth('any-photo.jpg', 500));
    }

    public function test_optimize_non_existent_file_returns_error_status(): void
    {
        $result = $this->service->optimizeImage($this->tempDir.DIRECTORY_SEPARATOR.'missing.jpg');

        $this->assertSame('error', $result['status']);
        $this->assertSame('File does not exist', $result['error']);
    }

    public function test_optimize_valid_jpeg_scales_and_compresses(): void
    {
        $testFile = $this->tempDir.DIRECTORY_SEPARATOR.'test-sample.jpg';

        // Create a 1600x1200 dummy JPEG
        $img = imagecreatetruecolor(1600, 1200);
        $color = imagecolorallocate($img, 100, 150, 200);
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $testFile, 100);
        imagedestroy($img);

        $this->assertFileExists($testFile);
        $initialSize = File::size($testFile);

        $result = $this->service->optimizeImage($testFile, ['quality' => 70]);

        $this->assertSame('success', $result['status']);
        $this->assertNull($result['error']);
        $this->assertLessThanOrEqual($initialSize, $result['new_size']);

        // Check scaled dimensions
        [$w, $h] = getimagesize($testFile);
        $this->assertLessThanOrEqual(1400, $w);
    }

    public function test_optimize_with_backup_creates_bak_file(): void
    {
        $testFile = $this->tempDir.DIRECTORY_SEPARATOR.'client-sample.jpg';

        $img = imagecreatetruecolor(800, 600);
        imagejpeg($img, $testFile, 95);
        imagedestroy($img);

        $result = $this->service->optimizeImage($testFile, ['backup' => true]);

        $this->assertSame('success', $result['status']);
        $this->assertFileExists($testFile.'.bak');

        [$w, $h] = getimagesize($testFile);
        $this->assertLessThanOrEqual(600, $w);
    }
}
