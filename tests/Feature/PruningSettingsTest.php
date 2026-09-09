<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DataPruningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruningSettingsTest extends TestCase
{
    use RefreshDatabase;

    private DataPruningService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = app(DataPruningService::class);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('system-tables.pruning'))->assertRedirect(route('login'));
        $this->post(route('system-tables.pruning.update'))->assertRedirect(route('login'));
        $this->post(route('system-tables.pruning.dry-run'))->assertRedirect(route('login'));
        $this->post(route('system-tables.pruning.execute'))->assertRedirect(route('login'));
        $this->post(route('system-tables.pruning.reset'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_pruning_settings_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get(route('system-tables.pruning'));

        $response->assertOk();
        $response->assertSee(__('Data Pruning & Lifecycle Management'));
        $response->assertSee('activity_log');
        $response->assertSee('notifications');
        $response->assertSee('failed_jobs');
        $response->assertSee(__('Save Pruning Settings'));
        $response->assertSee(__('Dry-Run Simulation'));
        $response->assertSee(__('Execute Pruning Now'));
    }

    public function test_updating_pruning_settings_persists_in_database_and_alters_engine_behavior(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $payload = [
            'enabled' => '1',
            'chunk_size' => 750,
            'tables' => [
                'activity_log' => [
                    'enabled' => '1',
                    'retention_days' => 45,
                    'max_records' => 80000,
                ],
                'notifications' => [
                    'enabled' => '1',
                    'retention_days' => 30,
                    'max_records' => 40000,
                    'only_read' => '1',
                ],
                'failed_jobs' => [
                    'enabled' => '0',
                    'retention_days' => 15,
                    'max_records' => 5000,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('system-tables.pruning.update'), $payload);

        $response->assertRedirect(route('system-tables.pruning'));
        $response->assertSessionHas('status');

        $stored = SystemSetting::get('data_pruning_settings');
        $this->assertNotNull($stored);
        $this->assertSame(750, $stored['chunk_size']);
        $this->assertSame(45, $stored['tables']['activity_log']['retention_days']);
        $this->assertTrue($stored['tables']['notifications']['only_read']);
        $this->assertFalse($stored['tables']['failed_jobs']['enabled']);

        // Check service effective config reflections
        $effective = $this->service->getEffectiveConfig();
        $this->assertSame(750, $effective['chunk_size']);
        $this->assertSame(45, $effective['tables']['activity_log']['retention_days']);
        $this->assertFalse($effective['tables']['failed_jobs']['enabled']);
    }

    public function test_dry_run_endpoint_returns_json_simulation_report(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)
            ->postJson(route('system-tables.pruning.dry-run'));

        $response->assertOk();
        $response->assertJsonStructure([
            'enabled',
            'dry_run',
            'tables',
            'total_pruned',
            'status',
        ]);
        $this->assertTrue($response->json('dry_run'));
    }

    public function test_immediate_execution_endpoint_triggers_pruning_and_redirects(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post(route('system-tables.pruning.execute'));

        $response->assertRedirect(route('system-tables.pruning'));
        $response->assertSessionHas('status');
    }

    public function test_reset_endpoint_restores_configuration_defaults(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        // Establish custom overrides first
        $this->service->saveCustomSettings([
            'enabled' => false,
            'chunk_size' => 123,
            'tables' => [
                'activity_log' => [
                    'retention_days' => 10,
                ],
            ],
        ]);
        $this->assertTrue($this->service->hasCustomSettings());

        $response = $this->actingAs($user)->post(route('system-tables.pruning.reset'));

        $response->assertRedirect(route('system-tables.pruning'));
        $response->assertSessionHas('status');

        $this->assertFalse($this->service->hasCustomSettings());
        $this->assertNull(SystemSetting::get('data_pruning_settings'));

        // Effective config should revert to default config/pruning.php values
        $this->assertSame(1000, $this->service->getEffectiveConfig()['chunk_size']);
    }

    public function test_pruning_audit_trail_operation_column_displays_translated_descriptions(): void
    {
        /** @var User $user */
        $user = User::factory()->superAdmin()->create();

        activity('data_pruning')
            ->log('Updated automated data pruning settings');

        // Arabic view
        app()->setLocale('ar');
        $responseAr = $this->actingAs($user)->get(route('system-tables.pruning'));
        $responseAr->assertOk();
        $responseAr->assertSee('تم تحديث إعدادات التقليم التلقائي للبيانات');

        // French view
        app()->setLocale('fr');
        $responseFr = $this->actingAs($user)->get(route('fr.system-tables.pruning'));
        $responseFr->assertOk();
        $responseFr->assertSee("Paramètres d'élagage automatique des données mis à jour");
    }
}
