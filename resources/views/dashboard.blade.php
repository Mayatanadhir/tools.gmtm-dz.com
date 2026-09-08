<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-transparent dark:border-gray-700/60 transition-colors duration-200">
                <div class="p-6 flex flex-col sm:flex-row items-center gap-4 sm:gap-5">
                    <x-application-logo class="w-12 h-12 shrink-0" />
                    <div class="text-center sm:text-start">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ __('Welcome to') }} ENGI-MATE
                        </h3>
                        <p class="text-xs sm:text-sm font-medium text-orange-600 dark:text-orange-400">
                            {{ __('Your Engineering Work Assistant') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ __("You're logged in!") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
