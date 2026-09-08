<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white dark:bg-orange-600 dark:hover:bg-orange-500 dark:active:bg-orange-700 border border-transparent rounded-xl font-semibold text-xs sm:text-sm tracking-wide shadow-sm shadow-orange-500/25 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150']) }}>
    {{ $slot }}
</button>
