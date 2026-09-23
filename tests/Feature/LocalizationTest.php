<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\View\Components\AppLayout;
use App\View\Components\GuestLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_locales_contains_only_ar_en_fr(): void
    {
        $supportedLocales = LaravelLocalization::getSupportedLocales();

        $this->assertArrayHasKey('ar', $supportedLocales);
        $this->assertArrayHasKey('en', $supportedLocales);
        $this->assertArrayHasKey('fr', $supportedLocales);
        $this->assertCount(3, $supportedLocales);
    }

    public function test_app_layout_component_renders_rtl_layout_for_arabic(): void
    {
        app()->setLocale('ar');
        $component = new AppLayout;
        $view = $component->render();

        $this->assertSame('layouts.app-rtl', $view->name());
    }

    public function test_app_layout_component_renders_ltr_layout_for_english(): void
    {
        app()->setLocale('en');
        $component = new AppLayout;
        $view = $component->render();

        $this->assertSame('layouts.app-ltr', $view->name());
    }

    public function test_app_layout_component_renders_ltr_layout_for_french(): void
    {
        app()->setLocale('fr');
        $component = new AppLayout;
        $view = $component->render();

        $this->assertSame('layouts.app-ltr', $view->name());
    }

    public function test_guest_layout_component_renders_rtl_layout_for_arabic(): void
    {
        app()->setLocale('ar');
        $component = new GuestLayout;
        $view = $component->render();

        $this->assertSame('layouts.guest-rtl', $view->name());
    }

    public function test_guest_layout_component_renders_ltr_layout_for_english(): void
    {
        app()->setLocale('en');
        $component = new GuestLayout;
        $view = $component->render();

        $this->assertSame('layouts.guest-ltr', $view->name());
    }

    public function test_guest_layout_component_renders_ltr_layout_for_french(): void
    {
        app()->setLocale('fr');
        $component = new GuestLayout;
        $view = $component->render();

        $this->assertSame('layouts.guest-ltr', $view->name());
    }

    public function test_json_translations_work_for_all_locales(): void
    {
        app()->setLocale('ar');
        $this->assertSame('مساحة العمل', __('Workspace'));
        $this->assertSame('الملف الشخصي', __('Profile'));

        app()->setLocale('en');
        $this->assertSame('Workspace', __('Workspace'));
        $this->assertSame('Profile', __('Profile'));

        app()->setLocale('fr');
        $this->assertSame('Espace de travail', __('Workspace'));
        $this->assertSame('Profil', __('Profile'));
    }

    public function test_php_language_lines_work_for_ar_en_fr(): void
    {
        app()->setLocale('ar');
        $this->assertSame('بيانات الاعتماد هذه غير متطابقة مع سجلاتنا.', __('auth.failed'));

        app()->setLocale('en');
        $this->assertSame('These credentials do not match our records.', __('auth.failed'));

        app()->setLocale('fr');
        $this->assertSame('Ces identifiants ne correspondent pas à nos enregistrements.', __('auth.failed'));
    }

    public function test_language_switcher_renders_all_supported_locales(): void
    {
        $rendered = Blade::render('<x-language-switcher />');

        $this->assertStringContainsString('العربية', $rendered);
        $this->assertStringContainsString('English', $rendered);
        $this->assertStringContainsString('Français', $rendered);
        $this->assertStringContainsString('hreflang="ar"', $rendered);
        $this->assertStringContainsString('hreflang="en"', $rendered);
        $this->assertStringContainsString('hreflang="fr"', $rendered);
    }

    public function test_first_visit_with_english_browser_redirects_to_english_locale(): void
    {
        config(['laravellocalization.useAcceptLanguageHeader' => true]);

        $response = $this->withHeaders(['Accept-Language' => 'en-US,en;q=0.9'])->get('/login');

        $response->assertStatus(302);
        $response->assertRedirect(url('/en'));
    }

    public function test_arabic_dashboard_renders_with_rtl_layout(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['locale' => 'ar'])
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('lang="ar"', false);
        $response->assertSee('مساحة العمل');
    }

    public function test_english_dashboard_renders_with_ltr_layout(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get('/en/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('lang="en"', false);
        $response->assertSee('Workspace');
    }

    public function test_french_dashboard_renders_with_ltr_layout(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['locale' => 'fr'])
            ->actingAs($user)
            ->get('/fr/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('lang="fr"', false);
        $response->assertSee('Espace de travail');
    }

    public function test_login_page_renders_guest_rtl_in_arabic(): void
    {
        $response = $this->withSession(['locale' => 'ar'])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('lang="ar"', false);
        $response->assertSee('تسجيل الدخول');
    }

    public function test_login_page_renders_guest_ltr_in_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/en/login');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('lang="en"', false);
        $response->assertSee('Log in');
    }

    public function test_all_locales_have_exact_translation_key_parity(): void
    {
        $en = json_decode((string) file_get_contents(base_path('lang/en.json')), true);
        $ar = json_decode((string) file_get_contents(base_path('lang/ar.json')), true);
        $fr = json_decode((string) file_get_contents(base_path('lang/fr.json')), true);

        $enKeys = array_keys($en);
        $arKeys = array_keys($ar);
        $frKeys = array_keys($fr);

        $missingInAr = array_diff($enKeys, $arKeys);
        $missingInFr = array_diff($enKeys, $frKeys);
        $extraInAr = array_diff($arKeys, $enKeys);
        $extraInFr = array_diff($frKeys, $enKeys);

        $this->assertEmpty($missingInAr, 'Keys present in en.json but missing in ar.json: '.implode(', ', $missingInAr));
        $this->assertEmpty($missingInFr, 'Keys present in en.json but missing in fr.json: '.implode(', ', $missingInFr));
        $this->assertEmpty($extraInAr, 'Keys present in ar.json but missing in en.json: '.implode(', ', $extraInAr));
        $this->assertEmpty($extraInFr, 'Keys present in fr.json but missing in en.json: '.implode(', ', $extraInFr));
    }

    /**
     * Ensure every translation key across all 3 dictionary files is strictly written in English (no Arabic characters in keys).
     */
    public function test_all_translation_keys_are_strictly_in_english(): void
    {
        $locales = ['en', 'ar', 'fr'];

        foreach ($locales as $locale) {
            $json = json_decode((string) file_get_contents(base_path("lang/{$locale}.json")), true);
            $nonEnglishKeys = [];

            foreach (array_keys($json) as $key) {
                // Check if key contains Arabic characters (Unicode range \x{0600}-\x{06FF})
                if (preg_match('/[\x{0600}-\x{06FF}]/u', (string) $key)) {
                    $nonEnglishKeys[] = $key;
                }
            }

            $this->assertEmpty(
                $nonEnglishKeys,
                "Found non-English (Arabic) translation keys in lang/{$locale}.json: ".implode(', ', $nonEnglishKeys)
            );
        }
    }
}
