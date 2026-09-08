<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\DataPruningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DynamicTablePruningTest extends TestCase
{
    use RefreshDatabase;

    private DataPruningService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = app(DataPruningService::class);

        // Create a dedicated dummy table for testing dynamic table pruning
        Schema::dropIfExists('test_metric_logs');
        Schema::create('test_metric_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('metric_name');
            $table->integer('metric_value');
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_metric_logs');

        parent::tearDown();
    }

    public function test_unauthenticated_requests_cannot_add_or_remove_pruning_tables(): void
    {
        $this->post(route('system-tables.pruning.tables.add'), [
            'table' => 'test_metric_logs',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => 30,
            'max_records' => 1000,
        ])->assertRedirect(route('login'));

        $this->delete(route('system-tables.pruning.tables.remove', 'test_metric_logs'))
            ->assertRedirect(route('login'));
    }

    public function test_eligible_tables_discovery_excludes_sovereign_and_already_configured_tables(): void
    {
        $eligible = $this->service->getEligibleTablesForPruning();
        $eligibleTableNames = array_column($eligible, 'name');

        // Sovereign protected tables must never be eligible
        $this->assertNotContains('users', $eligibleTableNames);
        $this->assertNotContains('roles', $eligibleTableNames);
        $this->assertNotContains('permissions', $eligibleTableNames);
        $this->assertNotContains('model_has_roles', $eligibleTableNames);
        $this->assertNotContains('migrations', $eligibleTableNames);
        $this->assertNotContains('sessions', $eligibleTableNames);
        $this->assertNotContains('cache', $eligibleTableNames);
        $this->assertNotContains('jobs', $eligibleTableNames);

        // Already configured built-in tables must not be eligible
        $this->assertNotContains('activity_log', $eligibleTableNames);
        $this->assertNotContains('notifications', $eligibleTableNames);
        $this->assertNotContains('failed_jobs', $eligibleTableNames);

        // Our test table should be present
        $this->assertContains('test_metric_logs', $eligibleTableNames);
    }

    public function test_attempting_to_onboard_sovereign_table_is_strictly_rejected(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('system-tables.pruning.tables.add'), [
            'table' => 'users',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => 30,
            'max_records' => 1000,
        ]);

        $response->assertSessionHasErrors('table');

        $effective = $this->service->getEffectiveConfig();
        $this->assertArrayNotHasKey('users', $effective['tables']);
    }

    public function test_authenticated_user_can_onboard_custom_table_and_view_in_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('system-tables.pruning.tables.add'), [
            'table' => 'test_metric_logs',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => 15,
            'max_records' => 500,
            'enabled' => '1',
        ]);

        $response->assertRedirect(route('system-tables.pruning'));
        $response->assertSessionHas('status');

        // Verify configuration persistence
        $effective = $this->service->getEffectiveConfig();
        $this->assertArrayHasKey('test_metric_logs', $effective['tables']);
        $this->assertTrue($effective['tables']['test_metric_logs']['is_custom']);
        $this->assertSame(15, $effective['tables']['test_metric_logs']['retention_days']);
        $this->assertSame(500, $effective['tables']['test_metric_logs']['max_records']);

        // Verify it renders on the dashboard
        $dashResponse = $this->actingAs($user)->get(route('system-tables.pruning'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('test_metric_logs');
        $dashResponse->assertSee(__('Custom'));
    }

    public function test_custom_table_pruning_executes_date_and_capacity_rules(): void
    {
        // Onboard custom table
        $this->service->addCustomTable([
            'table' => 'test_metric_logs',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => 10,
            'max_records' => 3,
            'enabled' => true,
        ]);

        // Insert 10 records: 5 old (expired), 5 fresh
        for ($i = 1; $i <= 5; $i++) {
            DB::table('test_metric_logs')->insert([
                'metric_name' => "metric_old_{$i}",
                'metric_value' => $i,
                'created_at' => now()->subDays(20),
            ]);
        }
        for ($i = 6; $i <= 10; $i++) {
            DB::table('test_metric_logs')->insert([
                'metric_name' => "metric_fresh_{$i}",
                'metric_value' => $i,
                'created_at' => now()->subDays(1),
            ]);
        }

        $this->assertSame(10, DB::table('test_metric_logs')->count());

        // Execute pruning
        $result = $this->service->pruneTable('test_metric_logs', dryRun: false);

        $this->assertSame('success', $result['status']);
        // 5 expired by date, and from remaining 5, capacity limit of 3 purges 2 more = 7 total purged
        $this->assertSame(5, $result['date_pruned']);
        $this->assertSame(2, $result['count_pruned']);
        $this->assertSame(7, $result['total_pruned']);
        $this->assertSame(3, $result['remaining_records']);
        $this->assertSame(3, DB::table('test_metric_logs')->count());
    }

    public function test_custom_table_can_be_removed_and_builtin_tables_cannot_be_removed(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // 1. Add custom table
        $this->service->addCustomTable([
            'table' => 'test_metric_logs',
            'primary_key' => 'id',
            'date_column' => 'created_at',
            'retention_days' => 10,
            'max_records' => 100,
            'enabled' => true,
        ]);

        $this->assertArrayHasKey('test_metric_logs', $this->service->getEffectiveConfig()['tables']);

        // 2. Remove custom table
        $response = $this->actingAs($user)->delete(route('system-tables.pruning.tables.remove', 'test_metric_logs'));
        $response->assertRedirect(route('system-tables.pruning'));
        $response->assertSessionHas('status');

        $this->assertArrayNotHasKey('test_metric_logs', $this->service->getEffectiveConfig()['tables']);

        // 3. Attempting to remove built-in table must fail
        $removeBuiltin = $this->actingAs($user)->delete(route('system-tables.pruning.tables.remove', 'activity_log'));
        $removeBuiltin->assertSessionHasErrors('table');
        $this->assertArrayHasKey('activity_log', $this->service->getEffectiveConfig()['tables']);
    }
}
