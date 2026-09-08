<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize
                            {path? : The absolute directory or file path to optimize}
                            {--backup : Create .bak backup before modifying original images}
                            {--quality=82 : Compression quality (1-100)}
                            {--max-width= : Force custom max width}
                            {--files=* : Specific filenames inside the directory to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize, scale down, and compress images safely';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizationService $service): int
    {
        $targetPath = $this->argument('path') ?? 'd:/Siteweb/gmtm-dz.com/public/images';
        $backup = (bool) $this->option('backup');
        $quality = (int) $this->option('quality');
        $maxWidth = $this->option('max-width') !== null ? (int) $this->option('max-width') : null;
        /** @var array<int, string> */
        $specificFiles = (array) $this->option('files');

        $options = [
            'backup' => $backup,
            'quality' => $quality,
            'max_width' => $maxWidth,
        ];

        $this->info(sprintf('Starting image optimization on: %s', $targetPath));
        if ($backup) {
            $this->comment('Safe mode enabled: backups (.bak) will be preserved.');
        }

        if (File::isFile($targetPath)) {
            $results = [$service->optimizeImage($targetPath, $options)];
        } elseif (File::isDirectory($targetPath)) {
            $results = $service->optimizeDirectory($targetPath, $specificFiles, $options);
        } else {
            $this->error(sprintf('The specified path does not exist: %s', $targetPath));

            return self::FAILURE;
        }

        if (empty($results)) {
            $this->warn('No eligible images found to optimize.');

            return self::SUCCESS;
        }

        $tableRows = [];
        $totalOriginal = 0;
        $totalNew = 0;

        foreach ($results as $res) {
            $totalOriginal += $res['original_size'];
            $totalNew += $res['new_size'];

            $origFormatted = $res['original_size'] > 1048576
                ? sprintf('%.2f MB', $res['original_size'] / 1048576)
                : sprintf('%.1f KB', $res['original_size'] / 1024);

            $newFormatted = $res['new_size'] > 1048576
                ? sprintf('%.2f MB', $res['new_size'] / 1048576)
                : sprintf('%.1f KB', $res['new_size'] / 1024);

            $statusText = match ($res['status']) {
                'success' => '<info>OK</info>',
                'error' => '<error>FAILED</error>',
                default => '<comment>SKIPPED</comment>',
            };

            $tableRows[] = [
                $res['filename'],
                $origFormatted,
                $newFormatted,
                sprintf('-%.1f%%', $res['saved_percent']),
                $statusText,
                $res['error'] ?? '-',
            ];
        }

        $this->table(
            ['Filename', 'Original', 'Optimized', 'Saved', 'Status', 'Error / Details'],
            $tableRows
        );

        $totalSavedBytes = max(0, $totalOriginal - $totalNew);
        $totalSavedPercent = $totalOriginal > 0 ? round(($totalSavedBytes / $totalOriginal) * 100, 1) : 0.0;

        $this->newLine();
        $this->info(sprintf(
            'Optimization completed! Total Original: %.2f MB -> Optimized: %.2f MB (Saved %.1f%% / %.2f MB)',
            $totalOriginal / 1048576,
            $totalNew / 1048576,
            $totalSavedPercent,
            $totalSavedBytes / 1048576
        ));

        return self::SUCCESS;
    }
}
