<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use ZipArchive;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseBackupService $service;

    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = app(DatabaseBackupService::class);

        // Isolate test backup directory to prevent wiping real backups
        config(['backup.backup.name' => 'TestingBackup']);
        $this->backupDir = Storage::disk('local')->path('TestingBackup');

        File::ensureDirectoryExists($this->backupDir);
    }

    protected function tearDown(): void
    {
        // Clean up only isolated test directory
        if (File::exists($this->backupDir)) {
            File::deleteDirectory($this->backupDir);
        }

        Schema::dropIfExists('test_restore_table');
        Schema::dropIfExists('test_oldest_table');

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_login_for_all_backup_routes(): void
    {
        $this->get(route('system-tables.backups'))->assertRedirect(route('login'));
        $this->post(route('system-tables.backups.create'))->assertRedirect(route('login'));
        $this->get(route('system-tables.backups.download', 'test.zip'))->assertRedirect(route('login'));
        $this->delete(route('system-tables.backups.delete', 'test.zip'))->assertRedirect(route('login'));
        $this->post(route('system-tables.backups.restore'), ['file' => 'test.zip'])->assertRedirect(route('login'));
        $this->post(route('system-tables.backups.restore-oldest'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_backups_dashboard_with_empty_state(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        // Clear backup directory for this test
        if (File::exists($this->backupDir)) {
            File::cleanDirectory($this->backupDir);
        }

        $response = $this->actingAs($user)->get(route('system-tables.backups'));

        $response->assertOk();
        $response->assertViewIs('system.backups');
        $response->assertViewHas('backupData');
        $response->assertSee(__('No database backups available yet.'));
    }

    public function test_authenticated_user_can_access_backups_dashboard_with_existing_backups_and_badges(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $fileName1 = 'test_backup_1.zip';
        $fileName2 = 'test_backup_2.zip';

        $this->createTestZip($fileName1, 'SELECT 1;');
        sleep(1);
        $this->createTestZip($fileName2, 'SELECT 2;');

        $response = $this->actingAs($user)->get(route('system-tables.backups'));

        $response->assertOk();
        $response->assertViewIs('system.backups');
        $response->assertSee(__('Oldest Snapshot'));
        $response->assertSee(__('Latest Snapshot'));
        $response->assertSee(__('Restore Oldest Snapshot'));
        $response->assertSee($fileName1);
        $response->assertSee($fileName2);
    }

    public function test_service_correctly_identifies_oldest_and_newest_backups(): void
    {
        $oldFile = 'test_backup_old.zip';
        $newFile = 'test_backup_new.zip';

        $this->createTestZip($oldFile, 'SELECT 1;');
        sleep(1);
        $this->createTestZip($newFile, 'SELECT 2;');

        $data = $this->service->getBackups();

        $this->assertGreaterThanOrEqual(2, $data['total_count']);
        $this->assertNotNull($data['oldest_backup']);
        $this->assertNotNull($data['newest_backup']);

        // Newest should be index 0 and oldest should be last index
        $this->assertTrue($data['backups'][0]['is_newest']);
        $this->assertTrue($data['backups'][count($data['backups']) - 1]['is_oldest']);
    }

    public function test_sanitize_file_name_prevents_directory_traversal_and_invalid_extensions(): void
    {
        $valid = $this->service->sanitizeFileName('test_backup_2026.zip');
        $this->assertSame('test_backup_2026.zip', $valid);

        $this->expectException(InvalidArgumentException::class);
        $this->service->sanitizeFileName('../../malicious.zip');
    }

    public function test_sanitize_file_name_rejects_non_zip_extensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->sanitizeFileName('exploit.php');
    }

    public function test_authenticated_user_can_download_backup_archive(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $fileName = 'test_backup_download.zip';
        $this->createTestZip($fileName, 'SELECT 1;');

        $response = $this->actingAs($user)->get(route('system-tables.backups.download', $fileName));

        $response->assertOk();
        $this->assertStringContainsString('application/zip', (string) $response->headers->get('content-type'));
    }

    public function test_authenticated_user_can_delete_backup_archive(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $fileName = 'test_backup_delete.zip';
        $this->createTestZip($fileName, 'SELECT 1;');

        $fullPath = $this->backupDir.DIRECTORY_SEPARATOR.$fileName;
        $this->assertFileExists($fullPath);

        $response = $this->actingAs($user)->delete(route('system-tables.backups.delete', $fileName));

        $response->assertRedirect(route('system-tables.backups'));
        $response->assertSessionHas('status');
        $this->assertFileDoesNotExist($fullPath);

        $activity = Activity::where('log_name', 'database_backup')
            ->where('description', "Deleted backup snapshot '{$fileName}'")
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_authenticated_user_can_restore_from_backup_snapshot(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $fileName = 'test_backup_restore.zip';
        $sql = 'CREATE TABLE test_restore_table (id INTEGER PRIMARY KEY, note TEXT); INSERT INTO test_restore_table (id, note) VALUES (1, "Restored Successfully");';

        $this->createTestZip($fileName, $sql);

        $response = $this->actingAs($user)->post(route('system-tables.backups.restore'), [
            'file' => $fileName,
        ]);

        $response->assertRedirect(route('system-tables.backups'));
        $response->assertSessionHas('status');

        $this->assertTrue(Schema::hasTable('test_restore_table'));
        $row = DB::table('test_restore_table')->where('id', 1)->first();
        $this->assertNotNull($row);
        $this->assertSame('Restored Successfully', $row->note);

        $activity = Activity::where('log_name', 'database_backup')
            ->where('description', "Restored database state from snapshot '{$fileName}'")
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_authenticated_user_can_restore_oldest_backup(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        // Clear backup directory first to ensure predictable order
        if (File::exists($this->backupDir)) {
            File::cleanDirectory($this->backupDir);
        }

        $oldFile = 'test_backup_oldest.zip';
        $newFile = 'test_backup_newest.zip';

        $oldSql = 'CREATE TABLE test_oldest_table (id INTEGER PRIMARY KEY, marker TEXT); INSERT INTO test_oldest_table (id, marker) VALUES (1, "From Oldest Snapshot");';
        $newSql = 'SELECT 1;';

        $this->createTestZip($oldFile, $oldSql);
        sleep(1);
        $this->createTestZip($newFile, $newSql);

        $response = $this->actingAs($user)->post(route('system-tables.backups.restore-oldest'));

        $response->assertRedirect(route('system-tables.backups'));
        $response->assertSessionHas('status');

        $this->assertTrue(Schema::hasTable('test_oldest_table'));
        $row = DB::table('test_oldest_table')->where('id', 1)->first();
        $this->assertNotNull($row);
        $this->assertSame('From Oldest Snapshot', $row->marker);
    }

    public function test_restore_fails_gracefully_when_no_backups_available(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        if (File::exists($this->backupDir)) {
            File::cleanDirectory($this->backupDir);
        }

        $response = $this->actingAs($user)->post(route('system-tables.backups.restore-oldest'));

        $response->assertRedirect(route('system-tables.backups'));
        $response->assertSessionHasErrors('backup');
    }

    private function createTestZip(string $fileName, string $sqlContent): string
    {
        $filePath = $this->backupDir.DIRECTORY_SEPARATOR.$fileName;

        $zip = new ZipArchive;
        $result = $zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \RuntimeException("Could not create zip: {$filePath}");
        }

        $zip->addFromString('db-dumps/sqlite-database.sql', $sqlContent);
        $zip->close();

        return $filePath;
    }
}
