<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('images/LogoP.jpg') }}">

        <!-- Arabic Fonts (Noto Kufi Arabic) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-kufi-arabic:400,500,600&display=swap" rel="stylesheet" />

        <!-- Zero-FOUC Theme Script -->
        <script>
            (function() {
                const theme = localStorage.getItem('theme') || 'system';
                const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- RTL Isolated Bundle -->
        @vite(['resources/css/app-rtl.css', 'resources/js/app-rtl.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 bg-gray-100 dark:bg-gray-900 relative selection:bg-brand-500 selection:text-white">
            <div class="absolute top-4 sm:top-6 end-4 sm:end-6 flex items-center gap-2 z-10">
                <x-theme-switcher />
                <x-language-switcher />
            </div>

            <div class="flex flex-col items-center">
                <a href="/" class="flex flex-col items-center gap-2 group text-center">
                    <x-application-logo class="h-16 sm:h-20 w-auto max-w-[220px] transition-transform duration-200 group-hover:scale-105" />
                    <span class="text-xs sm:text-sm font-medium text-brand-700 dark:text-brand-400 -mt-1 tracking-wide">
                        {{ __('Generale Maintenance & Travaux Montage') }}
                    </span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 sm:px-8 bg-white dark:bg-gray-800 shadow-md border border-gray-200/80 dark:border-gray-700/60 rounded-2xl relative">
                <!-- Close / Return to Welcome Page -->
                <div class="flex justify-end -mt-2 -me-2 mb-1">
                    <a href="{{ url('/') }}"
                       class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-600"
                       title="{{ __('Close') }}"
                       aria-label="{{ __('Close') }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
