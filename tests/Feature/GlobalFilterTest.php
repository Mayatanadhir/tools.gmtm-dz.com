<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_global_filter_renders_in_users_view_with_search_input(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Alice Test Engineer']);

        $response = $this->actingAs($user)->get(route('system-tables.users'));

        $response->assertOk();
        $response->assertSee('x-ref="filterForm"', false);
        $response->assertSee('name="search"', false);
        $response->assertSee(__('Search by name or email...'));
        // Reset button should not be displayed when no active filters exist
        $response->assertDontSee('title="'.__('Reset Filters').'"', false);
    }

    public function test_global_filter_displays_reset_button_when_search_query_is_active(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Bob Mechanical Engineer']);

        $response = $this->actingAs($user)->get(route('system-tables.users', ['search' => 'Mechanical']));

        $response->assertOk();
        $response->assertSee('Bob Mechanical Engineer');
        // Reset button should be visible when search parameter is present
        $response->assertSee('title="'.__('Reset Filters').'"', false);
        $response->assertSee(route('system-tables.users'), false);
    }

    public function test_global_filter_select_filters_activity_log_by_event(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Audit Trigger']);

        $response = $this->actingAs($user)->get(route('system-tables.activity-log', ['event' => 'created']));

        $response->assertOk();
        $response->assertSee('name="event"', false);
        $response->assertSee('title="'.__('Reset Filters').'"', false);
    }

    public function test_global_filter_select_filters_notifications_by_status(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.notifications', ['status' => 'unread']));

        $response->assertOk();
        $response->assertSee('name="status"', false);
        $response->assertSee('title="'.__('Reset Filters').'"', false);
    }

    public function test_global_filter_components_render_correctly_in_blade(): void
    {
        $view = $this->blade('
            <x-global-filter action="/test-endpoint" :search="true" search-placeholder="Search records...">
                <x-global-filter.select name="type" :options="[\'a\' => \'Type A\', \'b\' => \'Type B\']" />
                <x-slot:sorting>
                    <x-global-filter.sort :options="[\'created_at\' => \'Date\', \'name\' => \'Name\']" />
                </x-slot:sorting>
            </x-global-filter>
        ');

        $view->assertSee('action="/test-endpoint"', false);
        $view->assertSee('placeholder="Search records..."', false);
        $view->assertSee('name="type"', false);
        $view->assertSee('Type A');
        $view->assertSee('Type B');
        $view->assertSee('name="sort_by"', false);
        $view->assertSee('name="sort_direction"', false);
    }

    public function test_pagination_retains_global_filter_query_string(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Lead Engineer']);

        // Create 20 users matching "Engineer" to trigger pagination (15 per page)
        User::factory()->count(20)->create(['name' => 'Staff Engineer']);

        $response = $this->actingAs($user)->get(route('system-tables.users', ['search' => 'Engineer']));

        $response->assertOk();
        // The paginator links should contain both page and search parameters
        $response->assertSee('search=Engineer', false);
        $response->assertSee('page=2', false);
    }

    public function test_security_unwhitelisted_query_parameters_do_not_leak_or_break_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-tables.users', [
            'search' => 'Engineer',
            'unauthorized_param' => 'DROP TABLE users',
            'password_probe' => '$2y$10$',
        ]));

        $response->assertOk();
        $response->assertSee(__('Users Table'));
    }
}
