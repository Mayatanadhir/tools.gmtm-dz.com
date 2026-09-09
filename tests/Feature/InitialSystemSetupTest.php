<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InitialSystemSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_url_redirects_to_system_setup_when_users_table_is_empty(): void
    {
        $this->assertEquals(0, User::count());

        $response = $this->get('/');

        $response->assertRedirect(route('system-tables.setup'));
    }

    public function test_setup_screen_renders_successfully_when_users_table_is_empty(): void
    {
        $this->assertEquals(0, User::count());

        $response = $this->get(route('system-tables.setup'));

        $response->assertOk();
        $response->assertViewIs('system.setup');
        $response->assertSeeText(__('Create Super Admin Account'));
        $response->assertSeeText(__('Assigned Role'));
        $response->assertSeeText('Super-Admin');
    }

    public function test_super_admin_account_can_be_created_via_setup_form(): void
    {
        $this->assertEquals(0, User::count());

        $response = $this->post(route('system-tables.setup.store'), [
            'name' => 'First Super Admin',
            'email' => 'firstadmin@example.com',
            'password' => 'supersecret123',
            'password_confirmation' => 'supersecret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');

        $user = User::where('email', 'firstadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('First Super Admin', $user->name);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Super-Admin'));
        $this->assertTrue(Hash::check('supersecret123', $user->password));

        $this->assertAuthenticatedAs($user);
    }

    public function test_setup_form_validates_password_confirmation_and_minimum_length(): void
    {
        $response = $this->post(route('system-tables.setup.store'), [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(0, User::count());

        $responseShort = $this->post(route('system-tables.setup.store'), [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $responseShort->assertSessionHasErrors(['password']);
        $this->assertEquals(0, User::count());
    }

    public function test_setup_routes_are_strictly_forbidden_when_users_already_exist(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->assertGreaterThan(0, User::count());

        // GET must return 404
        $getResponse = $this->get(route('system-tables.setup'));
        $getResponse->assertNotFound();

        // POST must return 403 (unauthorized form request) or 404
        $postResponse = $this->post(route('system-tables.setup.store'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'intruder123',
            'password_confirmation' => 'intruder123',
        ]);
        $postResponse->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_root_url_renders_welcome_page_when_users_already_exist(): void
    {
        User::factory()->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    public function test_setup_screen_renders_with_auto_migration_flag_when_tables_do_not_exist(): void
    {
        Schema::dropIfExists('users');
        $this->assertFalse(Schema::hasTable('users'));

        $response = $this->get(route('system-tables.setup'));

        $response->assertOk();
        $response->assertViewIs('system.setup');
        $response->assertSeeText(__('Auto-Migration Required'));
        $response->assertSeeText(__('Database tables will be built and initialized automatically upon launch.'));
    }

    public function test_initial_setup_runs_migrations_and_creates_super_admin_when_tables_do_not_exist(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        // Drop all tables to simulate a completely empty, brand new database
        Schema::dropAllTables();
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('migrations'));

        $response = $this->post(route('system-tables.setup.store'), [
            'name' => 'Auto Migrated Admin',
            'email' => 'automigrated@example.com',
            'password' => 'supersecret123',
            'password_confirmation' => 'supersecret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');

        $this->assertTrue(Schema::hasTable('users'));
        $user = User::where('email', 'automigrated@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Auto Migrated Admin', $user->name);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Super-Admin'));
    }

    public function test_unmigrated_database_with_database_session_driver_gracefully_falls_back(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        Schema::dropAllTables();
        $this->assertFalse(Schema::hasTable('sessions'));

        config(['session.driver' => 'database', 'cache.default' => 'database']);

        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        $this->assertSame('file', config('session.driver'));
        $this->assertSame('file', config('cache.default'));

        $response = $this->get('/');
        $response->assertRedirect(route('system-tables.setup'));

        $setupResponse = $this->get(route('system-tables.setup'));
        $setupResponse->assertOk();
    }
}
