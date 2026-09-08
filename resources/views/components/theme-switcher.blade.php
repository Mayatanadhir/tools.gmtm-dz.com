<div class="relative"
     x-data="{
         open: false,
         theme: localStorage.getItem('theme') || 'system',
         init() {
             this.applyTheme(this.theme);
             window.addEventListener('theme-changed', (e) => {
                 this.theme = e.detail;
             });
             const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
             mediaQuery.addEventListener('change', (e) => {
                 if (this.theme === 'system') {
                     document.documentElement.classList.toggle('dark', e.matches);
                 }
             });
         },
         setTheme(newTheme) {
             if (newTheme === 'system') {
                 localStorage.removeItem('theme');
             } else {
                 localStorage.setItem('theme', newTheme);
             }
             this.theme = newTheme;
             this.applyTheme(newTheme);
             window.dispatchEvent(new CustomEvent('theme-changed', { detail: newTheme }));
             this.open = false;
         },
         applyTheme(t) {
             const isDark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
             document.documentElement.classList.toggle('dark', isDark);
         }
     }"
     @click.outside="open = false"
     @close.stop="open = false">

    <!-- Trigger Button -->
    <button @click="open = ! open"
            type="button"
            aria-label="{{ __('Theme') }}"
            class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">

        <!-- Light Icon (Sun) -->
        <svg x-show="theme === 'light'" class="h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>

        <!-- Dark Icon (Moon) -->
        <svg x-show="theme === 'dark'" class="h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>

        <!-- System Icon (Monitor) -->
        <svg x-show="theme === 'system'" class="h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" />
        </svg>

        <span class="text-xs font-medium" x-text="theme === 'light' ? '{{ __('Light') }}' : (theme === 'dark' ? '{{ __('Dark') }}' : '{{ __('System') }}')"></span>

        <!-- Chevron Arrow -->
        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown Options -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 w-36 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 divide-y divide-gray-100 dark:divide-gray-700 end-0"
         @click="open = false"
         x-cloak>
        <div class="py-1">
            <!-- Light Button -->
            <button @click="setTheme('light')"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition duration-150"
                    :class="{ 'bg-gray-50 dark:bg-gray-700/50 font-bold text-indigo-600 dark:text-indigo-400': theme === 'light' }">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <span>{{ __('Light') }}</span>
                </div>
                <template x-if="theme === 'light'">
                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </button>

            <!-- Dark Button -->
            <button @click="setTheme('dark')"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition duration-150"
                    :class="{ 'bg-gray-50 dark:bg-gray-700/50 font-bold text-indigo-600 dark:text-indigo-400': theme === 'dark' }">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <span>{{ __('Dark') }}</span>
                </div>
                <template x-if="theme === 'dark'">
                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </button>

            <!-- System Button -->
            <button @click="setTheme('system')"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-700 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition duration-150"
                    :class="{ 'bg-gray-50 dark:bg-gray-700/50 font-bold text-indigo-600 dark:text-indigo-400': theme === 'system' }">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" />
                    </svg>
                    <span>{{ __('System') }}</span>
                </div>
                <template x-if="theme === 'system'">
                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </button>
        </div>
    </div>
</div>
