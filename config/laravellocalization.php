<?php

return [

    // Supported languages: Arabic (ar), English (en), French (fr)
    'supportedLocales' => [
        'ar' => [
            'name' => 'Arabic',
            'script' => 'Arab',
            'native' => 'العربية',
            'regional' => 'ar_DZ',
        ],
        'en' => [
            'name' => 'English',
            'script' => 'Latn',
            'native' => 'English',
            'regional' => 'en_GB',
        ],
        'fr' => [
            'name' => 'French',
            'script' => 'Latn',
            'native' => 'Français',
            'regional' => 'fr_FR',
        ],
    ],

    // Automatically determine locale from browser on first visit
    'useAcceptLanguageHeader' => env('LARAVELLOCALIZATION_USE_ACCEPT_LANGUAGE_HEADER', true),

    // Hide default locale in URL (e.g. /dashboard for ar, /en/dashboard for en)
    'hideDefaultLocaleInURL' => true,

    // Locales display order in switcher
    'localesOrder' => ['ar', 'en', 'fr'],

    'localesMapping' => [],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    // URLs to ignore from localization routing
    'urlsIgnored' => ['/skipped', '/api/*'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
