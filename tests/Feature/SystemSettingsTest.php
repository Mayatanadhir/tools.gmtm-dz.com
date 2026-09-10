<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_is_open_by_default_and_cached(): void
    {
        Cache::flush();

        $this->assertTrue(is_registration_open());
        $this->assertTrue(SystemSetting::getBool('allow_registration', true));
    }

    public function test_guest_is_redirected_from_system_settings(): void
    {
        $this->get(route('system-tables.settings'))
            ->assertRedirect(route('login'));

        $this->post(route('system-tables.settings.toggle-registration'))
            ->assertRedirect(route('login'));
    }

    public function test_standard_user_cannot_access_system_settings(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('system-tables.settings'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('system-tables.settings.toggle-registration'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_system_settings_dashboard(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('system-tables.settings'));

        $response->assertOk();
        $response->assertSee(__('System Settings'));
        $response->assertSee(__('Allow new user registrations'));
        $response->assertSee(__('Active Configuration Registry'));
        $response->assertSee('allow_registration');
    }

    public function test_super_admin_can_toggle_registration_via_ajax(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        // 1. Toggle to disabled
        $response = $this->actingAs($superAdmin)
            ->postJson(route('system-tables.settings.toggle-registration'), [
                'enabled' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'enabled' => false,
            ]);

        $this->assertFalse(is_registration_open());
        $this->assertFalse(SystemSetting::getBool('allow_registration', true));

        // 2. Toggle back to enabled
        $response2 = $this->actingAs($superAdmin)
            ->postJson(route('system-tables.settings.toggle-registration'), [
                'enabled' => true,
            ]);

        $response2->assertOk()
            ->assertJson([
                'success' => true,
                'enabled' => true,
            ]);

        $this->assertTrue(is_registration_open());
        $this->assertTrue(SystemSetting::getBool('allow_registration', true));
    }

    public function test_registration_routes_are_shielded_when_registration_is_closed(): void
    {
        SystemSetting::set('allow_registration', false, 'auth', 'Registration disabled for testing');
        $this->assertFalse(is_registration_open());

        // GET /register redirects to login with flash error
        $response = $this->get(route('register'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $response->assertSessionHasErrors(['registration_closed']);

        // POST /register redirects and rejects creation
        $postResponse = $this->post(route('register'), [
            'name' => 'Intruder User',
            'email' => 'intruder@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $postResponse->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', [
            'email' => 'intruder@example.com',
        ]);

        // JSON POST /register returns 403 Forbidden
        $jsonResponse = $this->postJson(route('register'), [
            'name' => 'Intruder User',
            'email' => 'intruder@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $jsonResponse->assertForbidden()
            ->assertJson([
                'message' => __('New user registrations are currently closed by administration.'),
            ]);
    }

    public function test_registration_routes_are_accessible_when_registration_is_open(): void
    {
        SystemSetting::set('allow_registration', true, 'auth', 'Registration enabled for testing');
        $this->assertTrue(is_registration_open());

        $this->get(route('register'))->assertOk();

        $postResponse = $this->post(route('register'), [
            'name' => 'Valid Registrant',
            'email' => 'valid@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $postResponse->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'valid@example.com',
        ]);
    }

    public function test_super_admin_can_update_arbitrary_system_setting(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->postJson(route('system-tables.settings.update'), [
                'key' => 'site_maintenance_mode',
                'value' => false,
                'group' => 'system',
                'description' => 'Site maintenance switch',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'key' => 'site_maintenance_mode',
            ]);

        $this->assertFalse(system_setting('site_maintenance_mode'));
        $this->assertDatabaseHas('system_settings', [
            'key' => 'site_maintenance_mode',
            'group' => 'system',
        ]);
    }

    public function test_users_explorer_displays_registration_status_badge(): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->superAdmin()->create();

        SystemSetting::set('allow_registration', true);
        $response = $this->actingAs($superAdmin)->get(route('system-tables.users'));
        $response->assertOk();
        $response->assertSee(__('Registration: Open'));

        SystemSetting::set('allow_registration', false);
        $response2 = $this->actingAs($superAdmin)->get(route('system-tables.users'));
        $response2->assertOk();
        $response2->assertSee(__('Registration: Closed'));
    }

    public function test_welcome_page_dynamically_hides_register_link_when_registration_is_closed(): void
    {
        // Ensure at least one user exists so welcome doesn't redirect to first-time setup
        User::factory()->create();

        SystemSetting::set('allow_registration', true);
        $this->get(route('welcome'))->assertSee(route('register'));

        SystemSetting::set('allow_registration', false);
        $this->get(route('welcome'))->assertDontSee(route('register'));
    }
}
