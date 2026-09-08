<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OptimizeImagesCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cli_images_'.uniqid();
        File::makeDirectory($this->tempDir, 0755, true, true);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    public function test_command_fails_gracefully_when_path_does_not_exist(): void
    {
        $nonExistent = $this->tempDir.DIRECTORY_SEPARATOR.'not_found';

        $this->artisan('images:optimize', ['path' => $nonExistent])
            ->expectsOutputToContain('The specified path does not exist')
            ->assertExitCode(1);
    }

    public function test_command_runs_successfully_and_optimizes_images(): void
    {
        $testFile = $this->tempDir.DIRECTORY_SEPARATOR.'client-1.jpg';

        $img = imagecreatetruecolor(800, 600);
        imagejpeg($img, $testFile, 95);
        imagedestroy($img);

        $this->artisan('images:optimize', [
            'path' => $this->tempDir,
            '--backup' => true,
        ])
            ->expectsOutputToContain('Optimization completed!')
            ->assertExitCode(0);

        $this->assertFileExists($testFile.'.bak');
    }
}
