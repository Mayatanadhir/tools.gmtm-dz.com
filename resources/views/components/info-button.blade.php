<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:active:bg-indigo-700 border border-transparent rounded-xl font-semibold text-xs sm:text-sm tracking-wide shadow-sm shadow-indigo-500/25 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150']) }}>
    {{ $slot }}
</button>
