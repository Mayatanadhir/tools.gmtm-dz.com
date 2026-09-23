<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Warranty;
use Database\Seeders\LegacyWarrantySeeder;
use Illuminate\Console\Command;

class ImportLegacyWarrantiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warranties:import-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and synchronize legacy bank guarantee records preserving original primary keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting legacy bank guarantees migration...');

        $seeder = new LegacyWarrantySeeder;
        $seeder->run();

        $warranties = Warranty::whereIn('id', [2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13])->orderBy('id')->get();

        $rows = $warranties->map(fn (Warranty $w) => [
            'ID' => $w->id,
            'Reference' => $w->reference,
            'Bank Name' => $w->bank_name,
            'Amount (DZD)' => number_format((float) $w->amount, 2, '.', ' '),
            'Start Date' => $w->started_at?->format('Y-m-d') ?? '—',
            'Status' => $w->status->label(),
            'Type' => $w->type->label(),
        ])->toArray();

        $this->table(
            ['ID', 'Reference', 'Bank Name', 'Amount (DZD)', 'Start Date', 'Status', 'Type'],
            $rows
        );

        $this->info("Successfully synchronized {$warranties->count()} legacy bank guarantee records.");

        return self::SUCCESS;
    }
}
