<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupConfigurationTest extends TestCase
{
    public function test_backup_configuration_is_loaded_and_valid(): void
    {
        $backupConfig = config('backup');

        $this->assertIsArray($backupConfig);
        $this->assertArrayHasKey('backup', $backupConfig);

        $backupDetails = $backupConfig['backup'];
        $this->assertContains('local', $backupDetails['destination']['disks']);
        $this->assertNotEmpty($backupDetails['source']['databases']);

        $mysqlDumpConfig = config('database.connections.mysql.dump');
        $this->assertIsArray($mysqlDumpConfig);
        $this->assertArrayHasKey('dump_binary_path', $mysqlDumpConfig);
        $this->assertTrue($mysqlDumpConfig['use_single_transaction']);
    }

    public function test_backup_commands_are_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('backup:run', $commands);
        $this->assertArrayHasKey('backup:clean', $commands);
        $this->assertArrayHasKey('backup:list', $commands);
        $this->assertArrayHasKey('backup:monitor', $commands);
    }

    public function test_backup_commands_are_scheduled(): void
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make(Schedule::class);

        $events = collect($schedule->events());

        $cleanEvent = $events->first(function (Event $event): bool {
            return str_contains((string) $event->command, 'backup:clean');
        });

        $runEvent = $events->first(function (Event $event): bool {
            return str_contains((string) $event->command, 'backup:run');
        });

        $this->assertNotNull($cleanEvent, 'The backup:clean command must be scheduled.');
        $this->assertSame('0 1 * * *', $cleanEvent->expression, 'backup:clean must run daily at 01:00.');

        $this->assertNotNull($runEvent, 'The backup:run command must be scheduled.');
        $this->assertSame('30 1 * * *', $runEvent->expression, 'backup:run must run daily at 01:30.');
    }

    public function test_backup_clean_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('backup:clean', ['--disable-notifications' => true]);

        $this->assertSame(0, $exitCode);
    }
}
