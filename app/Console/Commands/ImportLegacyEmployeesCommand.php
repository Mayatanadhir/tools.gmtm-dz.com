<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use Database\Seeders\LegacyEmployeeSeeder;
use Illuminate\Console\Command;

class ImportLegacyEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:import-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and synchronize legacy employee records preserving original primary keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting legacy employees migration...');

        $seeder = new LegacyEmployeeSeeder;
        $seeder->run();

        $employees = Employee::withTrashed()->whereIn('id', [1, 2, 3, 4, 5, 25, 26])->get();

        $rows = $employees->map(fn (Employee $e) => [
            'ID' => $e->id,
            'Full Name' => $e->full_name,
            'Registration' => $e->registration_number,
            'Position' => $e->position->value,
            'Salary' => number_format((float) $e->salary, 2).' DZD',
            'Daily Rate' => number_format((float) $e->daily_rate, 2).' DZD',
            'User ID' => $e->user_id ?? 'None',
            'Status' => $e->status->value,
        ])->toArray();

        $this->table(
            ['ID', 'Full Name', 'Registration', 'Position', 'Salary', 'Daily Rate', 'User ID', 'Status'],
            $rows
        );

        $this->info("Successfully synchronized {$employees->count()} legacy employee records.");

        return self::SUCCESS;
    }
}
