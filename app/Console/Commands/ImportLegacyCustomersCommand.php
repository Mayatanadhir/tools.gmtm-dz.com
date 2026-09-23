<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Customer;
use Database\Seeders\LegacyCustomerSeeder;
use Illuminate\Console\Command;

class ImportLegacyCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:import-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and synchronize legacy customer records preserving original primary keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting legacy customers migration...');

        $seeder = new LegacyCustomerSeeder;
        $seeder->run();

        $customers = Customer::whereIn('id', [15, 16, 17, 18])->get();

        $rows = $customers->map(fn (Customer $c) => [
            'ID' => $c->id,
            'Reference' => $c->reference ?? '—',
            'Company Name' => $c->company_name,
            'Short Name' => $c->short_name ?? '—',
            'Created At' => $c->created_at?->format('Y-m-d H:i:s') ?? '—',
        ])->toArray();

        $this->table(
            ['ID', 'Reference', 'Company Name', 'Short Name', 'Created At'],
            $rows
        );

        $this->info("Successfully synchronized {$customers->count()} legacy customer records.");

        return self::SUCCESS;
    }
}
