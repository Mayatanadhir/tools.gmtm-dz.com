<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white dark:bg-rose-600 dark:hover:bg-rose-500 dark:active:bg-rose-700 border border-transparent rounded-xl font-semibold text-xs sm:text-sm tracking-wide shadow-sm shadow-rose-500/25 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150']) }}>
    {{ $slot }}
</button>
