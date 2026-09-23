<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <x-tool-icon name="article-types" class="w-11 h-11 sm:w-12 sm:h-12 shrink-0" />
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                        {{ __('Classification of Articles') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('Contract product and item classifications') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @can('create article types')
                    <x-primary-button type="button" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Create') }}</span>
                    </x-primary-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-operations-tabs active="article-types" />
                </aside>

                <!-- Main Content -->
                <main class="flex-1 w-full min-w-0 space-y-6">
                    <x-table>
                        <x-slot:toolbar>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                                <x-tool-icon name="article-types" class="w-6 h-6 shrink-0" />
                                <span>{{ __('Classification of Articles') }}</span>
                            </h3>
                        </x-slot:toolbar>

                        <x-slot:header>
                            <x-table.th>ID</x-table.th>
                            <x-table.th>{{ __('Name') }}</x-table.th>
                            <x-table.th>{{ __('Description') }}</x-table.th>
                            <x-table.th>{{ __('Created At') }}</x-table.th>
                            <x-table.th class="text-end">{{ __('Actions') }}</x-table.th>
                        </x-slot:header>

                        <x-table.empty :colspan="5" :message="__('No records found.')" />
                    </x-table>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
