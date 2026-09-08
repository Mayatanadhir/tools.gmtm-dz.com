@php
    $currentLocale = app()->getLocale();
    $locales = LaravelLocalization::getSupportedLocales();
    $localeFlags = [
        'ar' => '🇩🇿',
        'en' => '🇬🇧',
        'fr' => '🇫🇷',
    ];
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <button @click="open = ! open" type="button" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
        <span class="text-base">{{ $localeFlags[$currentLocale] ?? '🌐' }}</span>
        <span class="font-semibold">{{ $locales[$currentLocale]['native'] ?? strtoupper($currentLocale) }}</span>
        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 w-44 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 divide-y divide-gray-100 dark:divide-gray-700 end-0"
         @click="open = false"
         x-cloak>
        <div class="py-1">
            @foreach($locales as $localeCode => $properties)
                <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                   rel="alternate"
                   hreflang="{{ $localeCode }}"
                   class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition duration-150 {{ $localeCode === $currentLocale ? 'bg-gray-50 dark:bg-gray-700/50 font-bold text-indigo-600 dark:text-indigo-400' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ $localeFlags[$localeCode] ?? '🌐' }}</span>
                        <span>{{ $properties['native'] }}</span>
                    </div>
                    @if($localeCode === $currentLocale)
                        <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
