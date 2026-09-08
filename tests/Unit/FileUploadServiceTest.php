<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        $this->service = new FileUploadService;
    }

    public function test_upload_file_stores_file_with_unique_name(): void
    {
        $file = UploadedFile::fake()->image('document.png');

        $path = $this->service->uploadFile($file, 'documents');

        $this->assertStringStartsWith('documents/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_file_supports_custom_disk_and_directory(): void
    {
        $file = UploadedFile::fake()->create('contract.pdf', 100);

        $path = $this->service->uploadFile($file, 'contracts/2026', 'local');

        $this->assertStringStartsWith('contracts/2026/', $path);
        $this->assertStringEndsWith('.pdf', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_upload_file_accepts_whitelisted_extensions(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $path = $this->service->uploadFile($file, 'avatars', 'public', ['jpg', 'png']);

        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_file_rejects_non_whitelisted_extensions(): void
    {
        $file = UploadedFile::fake()->create('report.pdf', 50);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File extension [pdf] is not permitted.');

        $this->service->uploadFile($file, 'reports', 'public', ['jpg', 'png']);
    }

    public function test_upload_file_rejects_dangerous_extensions(): void
    {
        $file = UploadedFile::fake()->create('exploit.php', 10);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File extension [php] is dangerous and strictly forbidden.');

        $this->service->uploadFile($file, 'uploads');
    }

    public function test_delete_file_removes_existing_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $this->service->uploadFile($file, 'photos');

        Storage::disk('public')->assertExists($path);

        $deleted = $this->service->deleteFile($path);

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_file_returns_false_for_non_existent_file_or_empty_path(): void
    {
        $this->assertFalse($this->service->deleteFile('non_existent.jpg'));
        $this->assertFalse($this->service->deleteFile(null));
        $this->assertFalse($this->service->deleteFile(''));
    }

    public function test_replace_file_uploads_new_and_removes_old(): void
    {
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $this->service->uploadFile($oldFile, 'profiles');

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new.png');
        $newPath = $this->service->replaceFile($newFile, $oldPath, 'profiles');

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_file_exists_and_get_size_and_get_url(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.txt', 'Sample text content for size testing');
        $path = $this->service->uploadFile($file, 'files');

        $this->assertTrue($this->service->fileExists($path));
        $this->assertFalse($this->service->fileExists('invalid.txt'));
        $this->assertFalse($this->service->fileExists(null));

        $this->assertGreaterThan(0, $this->service->getSize($path));
        $this->assertStringContainsString($path, $this->service->getUrl($path));
    }
}
