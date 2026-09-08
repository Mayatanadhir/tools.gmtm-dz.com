<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white dark:bg-amber-600 dark:hover:bg-amber-500 dark:active:bg-amber-700 border border-transparent rounded-xl font-semibold text-xs sm:text-sm tracking-wide shadow-sm shadow-amber-500/25 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150']) }}>
    {{ $slot }}
</button>
