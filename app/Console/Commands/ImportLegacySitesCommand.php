<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Site;
use Database\Seeders\LegacySiteSeeder;
use Illuminate\Console\Command;

class ImportLegacySitesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sites:import-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and synchronize legacy site records preserving original primary keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting legacy sites migration...');

        $seeder = new LegacySiteSeeder;
        $seeder->run();

        $sites = Site::with('customer')->whereIn('id', [1, 3, 4, 5, 6, 13, 14])->get();

        $rows = $sites->map(fn (Site $s) => [
            'ID' => $s->id,
            'Code' => $s->site_code ?? '—',
            'Name' => $s->full_name ?? ($s->short_name ?? '—'),
            'Customer' => $s->customer?->short_name ?: ($s->customer?->company_name ?? '—'),
            'Location' => $s->location ?? '—',
            'Map Link' => ! empty($s->map_link) ? 'Yes' : 'No',
        ])->toArray();

        $this->table(
            ['ID', 'Code', 'Name', 'Customer', 'Location', 'Map Link'],
            $rows
        );

        $this->info("Successfully synchronized {$sites->count()} legacy site records.");

        return self::SUCCESS;
    }
}
