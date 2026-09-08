<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataPruningService;
use Illuminate\Console\Command;
use Throwable;

class DataPruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:prune
                            {--table= : Specific table to prune (e.g. activity_log, notifications, failed_jobs)}
                            {--dry-run : Simulate the pruning process without deleting any records}
                            {--chunk= : Override default chunk batch deletion size}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old or excess records from operational system tables according to lifecycle rules';

    /**
     * Execute the console command.
     */
    public function handle(DataPruningService $service): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $chunkOption = $this->option('chunk');
        $chunkSize = $chunkOption !== null ? (int) $chunkOption : null;
        $tableOption = $this->option('table');

        if ($isDryRun) {
            $this->warn('-------------------------------------------------------------');
            $this->warn(' [DRY-RUN MODE] Simulation active — No records will be deleted.');
            $this->warn('-------------------------------------------------------------');
        }

        try {
            if ($tableOption !== null && trim((string) $tableOption) !== '') {
                $target = trim((string) $tableOption);
                $this->info("Pruning specific table: [{$target}]...");

                $result = $service->pruneTable($target, $isDryRun, $chunkSize);
                $rows = [[
                    $result['table'],
                    number_format($result['date_pruned']),
                    number_format($result['count_pruned']),
                    number_format($result['total_pruned']),
                    number_format($result['remaining_records']),
                    $result['status'],
                ]];

                $this->table(
                    ['Table', 'Date Pruned', 'Capacity Pruned', 'Total Pruned', 'Remaining Records', 'Status'],
                    $rows
                );

                $total = $result['total_pruned'];
            } else {
                $this->info('Running automated data pruning across all configured tables...');
                $report = $service->pruneAll($isDryRun, $chunkSize);

                if (! $report['enabled']) {
                    $this->warn($report['message'] ?? 'Data pruning is disabled globally.');

                    return self::SUCCESS;
                }

                $rows = [];
                foreach ($report['tables'] as $tableResult) {
                    $rows[] = [
                        $tableResult['table'],
                        number_format($tableResult['date_pruned']),
                        number_format($tableResult['count_pruned']),
                        number_format($tableResult['total_pruned']),
                        number_format($tableResult['remaining_records']),
                        $tableResult['status'],
                    ];
                }

                $this->table(
                    ['Table', 'Date Pruned', 'Capacity Pruned', 'Total Pruned', 'Remaining Records', 'Status'],
                    $rows
                );

                $total = $report['total_pruned'];
            }

            if ($isDryRun) {
                $this->info("Dry run completed. Target records that would be pruned: {$total}");
            } else {
                $this->info("Pruning completed successfully. Total records purged: {$total}");
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Data pruning failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
