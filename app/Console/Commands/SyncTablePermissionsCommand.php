<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PermissionDiscoveryService;
use Illuminate\Console\Command;

class SyncTablePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-tables
                            {--dry-run : Simulate table discovery and permission generation without writing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize standard CRUD permissions from the static permissions catalog into database';

    /**
     * Execute the console command.
     */
    public function handle(PermissionDiscoveryService $service): int
    {
        $this->info('Synchronizing static permissions registry...');

        $tables = $service->getDiscoveredTables();
        $isDryRun = (bool) $this->option('dry-run');

        if (empty($tables)) {
            $this->warn('No business modules configured in permissions catalog.');

            return self::SUCCESS;
        }

        $this->line('Configured <fg=cyan>'.count($tables).'</> business modules:');
        $rows = [];

        foreach ($tables as $index => $table) {
            $rows[] = [
                '#' => $index + 1,
                'Table / Entity' => $table,
                'Standard CRUD Permissions' => implode(', ', array_map(fn (string $act): string => "{$act} {$table}", PermissionDiscoveryService::CRUD_ACTIONS)),
            ];
        }

        $this->table(['#', 'Table / Entity', 'Standard CRUD Permissions'], $rows);

        if ($isDryRun) {
            $this->warn('[DRY-RUN MODE] Static catalog inspected. No permissions were created in the database.');

            return self::SUCCESS;
        }

        $result = $service->generateCrudPermissionsForTables($tables);
        $newCount = count($result['permissions_created']);
        $prunedCount = count($result['permissions_pruned']);

        if ($prunedCount > 0) {
            $this->warn("Pruned <fg=red>{$prunedCount}</> obsolete permissions for non-existent tables:");
            foreach ($result['permissions_pruned'] as $oldPerm) {
                $this->line(" - <fg=red>[-]</> {$oldPerm}");
            }
        }

        if ($newCount > 0) {
            $this->info("Successfully generated <fg=green>{$newCount}</> new permissions:");
            foreach ($result['permissions_created'] as $newPerm) {
                $this->line(" - <fg=green>[+]</> {$newPerm}");
            }
        } else {
            $this->info('All standard CRUD permissions are already synchronized in the database.');
        }

        $superAdminCount = $service->syncSuperAdminPermissions();
        $superRolesList = implode(', ', $service->getSuperRoles());
        $this->info("Synchronized all <fg=yellow>{$superAdminCount}</> active permissions with the super roles: <fg=bright-white>{$superRolesList}</>.");

        return self::SUCCESS;
    }
}
