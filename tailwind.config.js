import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ── GMTM Brand Scale (driven by tokens.css) ──
                brand: {
                    50:  'var(--color-brand-50)',
                    100: 'var(--color-brand-100)',
                    200: 'var(--color-brand-200)',
                    300: 'var(--color-brand-300)',
                    400: 'var(--color-brand-400)',
                    500: 'var(--color-brand-500)',
                    600: 'var(--color-brand-600)',
                    700: 'var(--color-brand-700)',
                    800: 'var(--color-brand-800)',
                    900: 'var(--color-brand-900)',
                    950: 'var(--color-brand-950)',
                },
                // ── Semantic Aliases ──────────────────────────
                primary:  'var(--color-primary)',
                danger:   'var(--color-danger)',
                warning:  'var(--color-warning)',
                success:  'var(--color-success)',
                info:     'var(--color-info)',
            },
        },
    },

    plugins: [forms],

    safelist: [
        'translate-x-0',
        'translate-x-7',
        // brand shades used dynamically
        { pattern: /bg-brand-(50|100|200|500|600|700|800|950)/ },
        { pattern: /text-brand-(300|400|500|600|700)/ },
        { pattern: /border-brand-(200|600|800)/ },
        { pattern: /ring-brand-(500|600)/ },
    ],
};

