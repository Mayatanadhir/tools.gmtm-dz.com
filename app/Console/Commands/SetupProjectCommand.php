<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\PermissionDiscoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SetupProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:setup
                            {--fresh : Drop all tables and re-run all migrations}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations, seed base data, and create the initial Super Admin account interactively';

    /**
     * Execute the console command.
     */
    public function handle(PermissionDiscoveryService $discoveryService): int
    {
        $this->info('=============================================');
        $this->info('🚀 Starting Project Setup & Database Initialization');
        $this->info('=============================================');

        $isFresh = (bool) $this->option('fresh');
        $isForce = (bool) $this->option('force');

        // 1. Run migrations
        $this->newLine();
        $migrationCommand = $isFresh ? 'migrate:fresh' : 'migrate';
        $this->info("Running database migrations ({$migrationCommand})...");

        $migrationExitCode = $this->call($migrationCommand, [
            '--force' => $isForce || app()->environment('production'),
        ]);

        if ($migrationExitCode !== 0) {
            $this->error('Database migrations failed. Setup aborted.');

            return self::FAILURE;
        }

        $this->info('✔ Database migrations completed successfully.');

        // 2. Seed base data (Roles, Permissions discovery, baseline data)
        $this->newLine();
        $this->info('Seeding baseline database data & permissions...');

        $seederExitCode = $this->call('db:seed', [
            '--force' => $isForce || app()->environment('production'),
        ]);

        if ($seederExitCode !== 0) {
            $this->error('Database seeding failed. Setup aborted.');

            return self::FAILURE;
        }

        $this->info('✔ Baseline database data seeded successfully.');

        // 3. Super Admin Interactive Onboarding
        $this->newLine();
        $this->info('---------------------------------------------');
        $this->info('🔐 Initial Super Admin Account Setup');
        $this->info('---------------------------------------------');

        $name = (string) $this->ask('Enter Super Admin Name', 'Super Admin');
        $email = (string) $this->ask('Enter Super Admin Email');

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email address provided. Setup aborted.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Enter Super Admin Password');
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long. Setup aborted.');

            return self::FAILURE;
        }

        $confirmPassword = (string) $this->secret('Confirm Super Admin Password');
        if ($password !== $confirmPassword) {
            $this->error('Passwords do not match. Setup aborted.');

            return self::FAILURE;
        }

        // 4. Create or Update Super Admin User
        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'status' => AccountStatus::Active,
            ]
        );

        $admin->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $admin->syncRoles(['Super-Admin']);

        // Sync all discovered CRUD permissions to Super-Admin role
        $discoveryService->syncSuperAdminPermissions();

        $this->newLine();
        $this->info('=============================================');
        $this->info("✔ Super Admin [{$admin->email}] setup successfully!");
        $this->info('=============================================');

        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $admin->name],
                ['Email', $admin->email],
                ['Role', 'Super-Admin'],
                ['Status', $admin->status->value],
                ['Email Verified', $admin->email_verified_at?->toDateTimeString() ?? 'N/A'],
            ]
        );

        $this->info('🎉 Project is fully initialized and ready for production use!');

        return self::SUCCESS;
    }
}
