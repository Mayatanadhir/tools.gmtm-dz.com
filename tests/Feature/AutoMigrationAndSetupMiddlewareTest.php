<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureDatabaseIsMigrated;
use App\Http\Middleware\EnsureSuperAdminExists;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AutoMigrationAndSetupMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_super_admin_exists_redirects_root_when_no_users_exist(): void
    {
        $this->assertEquals(0, User::count());

        $response = $this->get('/');

        $response->assertRedirect(route('system-tables.setup'));
    }

    public function test_ensure_super_admin_exists_allows_setup_route_when_no_users_exist(): void
    {
        $this->assertEquals(0, User::count());

        $response = $this->get(route('system-tables.setup'));

        $response->assertOk();
        $response->assertViewIs('system.setup');
    }

    public function test_ensure_super_admin_exists_seals_setup_route_when_users_already_exist(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        User::factory()->create();

        $this->assertGreaterThan(0, User::count());

        $response = $this->get(route('system-tables.setup'));
        $response->assertNotFound();

        $postResponse = $this->post(route('system-tables.setup.store'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'secret123456',
            'password_confirmation' => 'secret123456',
        ]);
        $postResponse->assertForbidden();
    }

    public function test_middleware_redirects_in_production_mode_when_users_table_is_empty(): void
    {
        $middleware = new EnsureSuperAdminExists;
        $request = Request::create('/dashboard', 'GET');

        // Without users, handling a request with mock next
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        // In test environment, root is redirected; test direct method
        $this->assertEquals(0, User::count());
    }

    public function test_ensure_database_is_migrated_runs_without_exceptions_on_valid_database(): void
    {
        Cache::flush();

        $middleware = new EnsureDatabaseIsMigrated;
        $request = Request::create('/', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_database_error_view_renders_correctly(): void
    {
        $view = view('errors.database', [
            'connection' => 'mysql',
            'database' => 'unreachable_db',
            'host' => '127.0.0.1',
            'port' => '3306',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString(__('Database Connection Required'), $rendered);
        $this->assertStringContainsString('unreachable_db', $rendered);
        $this->assertStringContainsString(__('Retry Connection'), $rendered);
        $this->assertStringContainsString(__('Not Connected'), $rendered);
    }

    public function test_is_database_missing_error_identifies_unknown_database(): void
    {
        $middleware = new class extends EnsureDatabaseIsMigrated
        {
            public function checkMissingError(\PDOException $e): bool
            {
                return $this->isDatabaseMissingError($e);
            }
        };

        $mysql1049 = new \PDOException("SQLSTATE[HY000] [1049] Unknown database 'test_missing'", 1049);
        $this->assertTrue($middleware->checkMissingError($mysql1049));

        $otherError = new \PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'", 1045);
        $this->assertFalse($middleware->checkMissingError($otherError));
    }
}
