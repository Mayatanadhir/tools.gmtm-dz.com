<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetupProjectCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_project_command_runs_successfully_and_creates_super_admin(): void
    {
        $this->artisan('project:setup')
            ->expectsQuestion('Enter Super Admin Name', 'Super Admin')
            ->expectsQuestion('Enter Super Admin Email', 'superadmin@example.com')
            ->expectsQuestion('Enter Super Admin Password', 'password1234')
            ->expectsQuestion('Confirm Super Admin Password', 'password1234')
            ->expectsOutputToContain('Super Admin [superadmin@example.com] setup successfully!')
            ->assertExitCode(0);

        $user = User::where('email', 'superadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Super Admin', $user->name);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Super-Admin'));
        $this->assertTrue(Hash::check('password1234', $user->password));
    }

    public function test_setup_project_command_fails_when_passwords_do_not_match(): void
    {
        $this->artisan('project:setup')
            ->expectsQuestion('Enter Super Admin Name', 'Super Admin')
            ->expectsQuestion('Enter Super Admin Email', 'mismatch@example.com')
            ->expectsQuestion('Enter Super Admin Password', 'password1234')
            ->expectsQuestion('Confirm Super Admin Password', 'different_password')
            ->expectsOutputToContain('Passwords do not match. Setup aborted.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@example.com']);
    }

    public function test_setup_project_command_fails_when_password_is_too_short(): void
    {
        $this->artisan('project:setup')
            ->expectsQuestion('Enter Super Admin Name', 'Super Admin')
            ->expectsQuestion('Enter Super Admin Email', 'shortpwd@example.com')
            ->expectsQuestion('Enter Super Admin Password', 'short')
            ->expectsOutputToContain('Password must be at least 8 characters long. Setup aborted.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'shortpwd@example.com']);
    }

    public function test_setup_project_command_fails_when_email_is_invalid(): void
    {
        $this->artisan('project:setup')
            ->expectsQuestion('Enter Super Admin Name', 'Super Admin')
            ->expectsQuestion('Enter Super Admin Email', 'invalid-email-address')
            ->expectsOutputToContain('Invalid email address provided. Setup aborted.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'invalid-email-address']);
    }

    public function test_setup_project_command_updates_existing_user_and_assigns_super_admin(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Old Name',
        ]);

        $this->artisan('project:setup')
            ->expectsQuestion('Enter Super Admin Name', 'Promoted Admin')
            ->expectsQuestion('Enter Super Admin Email', 'existing@example.com')
            ->expectsQuestion('Enter Super Admin Password', 'new_secure_password123')
            ->expectsQuestion('Confirm Super Admin Password', 'new_secure_password123')
            ->expectsOutputToContain('Super Admin [existing@example.com] setup successfully!')
            ->assertExitCode(0);

        $existing->refresh();
        $this->assertSame('Promoted Admin', $existing->name);
        $this->assertTrue($existing->hasRole('Super-Admin'));
        $this->assertTrue(Hash::check('new_secure_password123', $existing->password));
    }

    public function test_setup_project_command_supports_fresh_flag(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        $this->artisan('project:setup', ['--fresh' => true])
            ->expectsQuestion('Enter Super Admin Name', 'Fresh Super Admin')
            ->expectsQuestion('Enter Super Admin Email', 'freshadmin@example.com')
            ->expectsQuestion('Enter Super Admin Password', 'freshpassword123')
            ->expectsQuestion('Confirm Super Admin Password', 'freshpassword123')
            ->expectsOutputToContain('Running database migrations (migrate:fresh)...')
            ->expectsOutputToContain('Super Admin [freshadmin@example.com] setup successfully!')
            ->assertExitCode(0);

        $user = User::where('email', 'freshadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Fresh Super Admin', $user->name);
        $this->assertTrue($user->hasRole('Super-Admin'));
    }
}
