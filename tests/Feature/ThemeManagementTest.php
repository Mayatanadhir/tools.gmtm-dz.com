<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ThemeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_fouc_prevention_script_is_present_in_all_layouts(): void
    {
        $layouts = [
            'resources/views/layouts/app-rtl.blade.php',
            'resources/views/layouts/app-ltr.blade.php',
            'resources/views/layouts/guest-rtl.blade.php',
            'resources/views/layouts/guest-ltr.blade.php',
            'resources/views/welcome.blade.php',
        ];

        foreach ($layouts as $layoutPath) {
            $content = File::get(base_path($layoutPath));
            $this->assertStringContainsString('localStorage.getItem(\'theme\')', $content, "Missing FOUC script in {$layoutPath}");
            $this->assertStringContainsString('prefers-color-scheme: dark', $content, "Missing media query in {$layoutPath}");
            $this->assertStringContainsString('classList.add(\'dark\')', $content, "Missing classList.add in {$layoutPath}");
            $this->assertStringContainsString('classList.remove(\'dark\')', $content, "Missing classList.remove in {$layoutPath}");
        }
    }

    public function test_theme_switcher_component_renders_correctly(): void
    {
        $rendered = Blade::render('<x-theme-switcher />');

        $this->assertStringContainsString('x-data', $rendered);
        $this->assertStringContainsString('setTheme(\'light\')', $rendered);
        $this->assertStringContainsString('setTheme(\'dark\')', $rendered);
        $this->assertStringContainsString('setTheme(\'system\')', $rendered);
        $this->assertStringContainsString('theme-changed', $rendered);
        $this->assertStringContainsString('prefers-color-scheme: dark', $rendered);
    }

    public function test_navigation_views_contain_theme_switcher(): void
    {
        $rtlContent = File::get(base_path('resources/views/layouts/navigation-rtl.blade.php'));
        $this->assertStringContainsString('<x-theme-switcher />', $rtlContent);
        $this->assertEquals(2, substr_count($rtlContent, '<x-theme-switcher />'), 'Theme switcher should appear in both desktop and mobile navigation in navigation-rtl');

        $ltrContent = File::get(base_path('resources/views/layouts/navigation-ltr.blade.php'));
        $this->assertStringContainsString('<x-theme-switcher />', $ltrContent);
        $this->assertEquals(2, substr_count($ltrContent, '<x-theme-switcher />'), 'Theme switcher should appear in both desktop and mobile navigation in navigation-ltr');
    }

    public function test_guest_layouts_contain_theme_switcher(): void
    {
        $rtlGuest = File::get(base_path('resources/views/layouts/guest-rtl.blade.php'));
        $this->assertStringContainsString('<x-theme-switcher />', $rtlGuest);

        $ltrGuest = File::get(base_path('resources/views/layouts/guest-ltr.blade.php'));
        $this->assertStringContainsString('<x-theme-switcher />', $ltrGuest);
    }

    public function test_welcome_view_contains_theme_switcher(): void
    {
        $welcome = File::get(base_path('resources/views/welcome.blade.php'));
        $this->assertStringContainsString('<x-theme-switcher />', $welcome);
    }

    public function test_theme_translations_exist_in_all_languages(): void
    {
        // Arabic
        $this->assertSame('فاتح', __('Light', [], 'ar'));
        $this->assertSame('داكن', __('Dark', [], 'ar'));
        $this->assertSame('تلقائي', __('System', [], 'ar'));
        $this->assertSame('المظهر', __('Theme', [], 'ar'));

        // French
        $this->assertSame('Clair', __('Light', [], 'fr'));
        $this->assertSame('Sombre', __('Dark', [], 'fr'));
        $this->assertSame('Système', __('System', [], 'fr'));
        $this->assertSame('Thème', __('Theme', [], 'fr'));

        // English
        $this->assertSame('Light', __('Light', [], 'en'));
        $this->assertSame('Dark', __('Dark', [], 'en'));
        $this->assertSame('System', __('System', [], 'en'));
        $this->assertSame('Theme', __('Theme', [], 'en'));
    }

    public function test_tailwind_config_enables_class_based_dark_mode(): void
    {
        $tailwindConfig = File::get(base_path('tailwind.config.js'));
        $this->assertMatchesRegularExpression('/darkMode\s*:\s*[\'"]class[\'"]/', $tailwindConfig);
    }

    public function test_authenticated_user_sees_theme_switcher_on_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('setTheme(\'light\')', false);
        $response->assertSee('setTheme(\'dark\')', false);
        $response->assertSee('setTheme(\'system\')', false);
    }

    public function test_guest_user_sees_theme_switcher_on_login(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('setTheme(\'light\')', false);
        $response->assertSee('setTheme(\'dark\')', false);
        $response->assertSee('setTheme(\'system\')', false);
    }
}
