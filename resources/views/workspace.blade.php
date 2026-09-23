<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-tool-icon name="workspace" class="w-14 h-14 shrink-0 transition-transform duration-200 hover:scale-105" />
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                        {{ __('Workspace') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('General information and statistics dashboard') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(Auth::user()?->isSuperAdmin() || Auth::user()?->hasRole('Super-Admin'))
                    <x-badge variant="primary" size="md">
                        {{ __('Super-Admin') }}
                    </x-badge>
                @elseif(Auth::user()?->roles->isNotEmpty())
                    <x-badge variant="info" size="md">
                        {{ Auth::user()->roles->first()->name }}
                    </x-badge>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Executive Welcome Banner -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700/60 p-6 transition-all duration-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4 sm:gap-5 text-center sm:text-start">
                        <x-application-logo class="h-14 w-auto shrink-0 max-w-[160px]" />
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ __('Welcome to') }} GMTM
                            </h3>
                            <p class="text-sm font-medium text-brand-700 dark:text-brand-400">
                                {{ __('Generale Maintenance & Travaux Montage') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Signed in as :name (:email)', ['name' => Auth::user()->name, 'email' => Auth::user()->email]) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('profile.edit') }}">
                            <x-secondary-button type="button" class="text-xs">
                                {{ __('Manage your Account') }}
                            </x-secondary-button>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enterprise Business Modules Grid -->
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>{{ __('Business Management Portals') }}</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <!-- 🔬 Metrology & Equipments Module Card -->
                    @can('view metrology')
                    <div class="relative flex flex-col justify-between rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60 hover:shadow-md transition-all duration-200 group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <x-tool-icon name="module-metrology" class="w-14 h-14 shrink-0 transition-transform duration-200 group-hover:scale-105" />
                                <x-badge variant="neutral" size="md">{{ __('5 Explorers') }}</x-badge>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors">
                                {{ __('Dashboard Metrology') }}
                            </h4>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Manage measuring instruments, technical equipment, calibrator movements, and calibration certificates.') }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <a href="{{ route('dashboard_metrology') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 dark:bg-brand-700 text-white shadow-sm transition">
                                <span>{{ __('Open Dashboard') }}</span> &rarr;
                            </a>
                        </div>
                    </div>
                    @endcan

                    <!-- 💼 Operations & Projects Module Card -->
                    @can('view operations')
                    <div class="relative flex flex-col justify-between rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60 hover:shadow-md transition-all duration-200 group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <x-tool-icon name="module-operations" class="w-14 h-14 shrink-0 transition-transform duration-200 group-hover:scale-105" />
                                <x-badge variant="neutral" size="md">{{ __('5 Explorers') }}</x-badge>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors">
                                {{ __('Dashboard Operations') }}
                            </h4>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Coordinate operational field missions, manage enterprise contracts.') }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <a href="{{ route('dashboard_operations') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 dark:bg-brand-700 text-white shadow-sm transition">
                                <span>{{ __('Open Dashboard') }}</span> &rarr;
                            </a>
                        </div>
                    </div>
                    @endcan

                    <!-- 📈 Internal & Analytical Management Module Card -->
                    @can('view analytics')
                    <div class="relative flex flex-col justify-between rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60 hover:shadow-md transition-all duration-200 group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <x-tool-icon name="module-analytics" class="w-14 h-14 shrink-0 transition-transform duration-200 group-hover:scale-105" />
                                <x-badge variant="neutral" size="md">{{ __('4 Explorers') }}</x-badge>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors">
                                {{ __('Dashboard Analytics') }}
                            </h4>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Track financial expenditures, analyze annual performance forecasts, company metrics, and executive reports.') }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <a href="{{ route('dashboard_analytics') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 dark:bg-brand-700 text-white shadow-sm transition">
                                <span>{{ __('Open Dashboard') }}</span> &rarr;
                            </a>
                        </div>
                    </div>
                    @endcan

                    <!-- 🗂️ Master Data Module Card -->
                    @can('view master data')
                    <div class="relative flex flex-col justify-between rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60 hover:shadow-md transition-all duration-200 group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <x-tool-icon name="module-master-data" class="w-14 h-14 shrink-0 transition-transform duration-200 group-hover:scale-105" />
                                <x-badge variant="neutral" size="md">{{ __('3 Explorers') }}</x-badge>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors">
                                {{ __('Dashboard Master Data') }}
                            </h4>
                            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Maintain foundational reference data including clients, employees, sites, units, and article classifications.') }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <a href="{{ route('dashboard_master_data') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 dark:bg-brand-700 text-white shadow-sm transition">
                                <span>{{ __('Open Dashboard') }}</span> &rarr;
                            </a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Super-Admin System Control Banner -->
            @if(Auth::user()?->isSuperAdmin() || Auth::user()?->hasRole('Super-Admin'))
            <div class="rounded-2xl bg-gradient-to-r from-gray-900 to-gray-800 text-white p-6 shadow-sm border border-gray-700/60">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-tool-icon name="module-system" class="w-14 h-14 shrink-0 transition-transform duration-200 hover:scale-105" />
                        <div>
                            <h4 class="text-base font-bold text-white">{{ __('System Control & Infrastructure') }}</h4>
                            <p class="text-xs text-gray-300 mt-0.5">{{ __('Access low-level database tables, audit activity logs, queue monitors, backups, and security settings.') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('system-tables.index') }}" class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition">
                        <span>{{ __('System Control') }}</span> &rarr;
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
