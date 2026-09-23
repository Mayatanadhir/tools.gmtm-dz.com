@props(['href' => '#', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center gap-2.5 px-4 py-2 text-sm
           {{ $active
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold'
                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white' }}
           transition-colors duration-150 rounded-lg mx-1"
>
    {{ $slot }}
</a>
