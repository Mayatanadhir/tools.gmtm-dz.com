import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Original bundle (kept for backward compatibility)
                'resources/css/app.css',
                'resources/js/app.js',
                // Arabic RTL isolated bundle
                'resources/css/app-rtl.css',
                'resources/js/app-rtl.js',
                // LTR (en / fr) isolated bundle
                'resources/css/app-ltr.css',
                'resources/js/app-ltr.js',
            ],
            refresh: true,
        }),
    ],
});
