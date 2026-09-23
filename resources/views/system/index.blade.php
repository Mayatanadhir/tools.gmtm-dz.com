<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-tool-icon name="module-system" class="w-14 h-14 shrink-0 transition-transform duration-200 hover:scale-105" />
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight">
                        {{ __('System & Database') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('Monitor and inspect all application database tables and system state') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-badge variant="primary" size="md" :dot="true" :dot-ping="true">
                    13 {{ __('System Control') }}
                </x-badge>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 shrink-0">
                    <x-system-tabs active="index" />
                </aside>

                <!-- Main Content -->
                <main class="flex-1 w-full min-w-0 space-y-6">
                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        <!-- Users Card -->
                        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700/60 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Total Users') }}</p>
                                    <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['users_count']) }}</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['sessions_count'] }} {{ __('Active Sessions') }}</p>
                                </div>
                                <div class="rounded-xl bg-brand-500/10 dark:bg-brand-500/20 p-3 text-brand-700 dark:text-brand-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-100 dark:border-gray-700/60 pt-3">
                                <a href="{{ route('system-tables.users') }}" class="inline-flex items-center text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                    <span>{{ __('Details') }}</span> &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Roles & Permissions Card -->
                        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700/60 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Active Roles') }}</p>
                                    <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['roles_count']) }}</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['permissions_count'] }} {{ __('Permissions') }}</p>
                                </div>
                                <div class="rounded-xl bg-brand-500/10 dark:bg-brand-500/20 p-3 text-brand-700 dark:text-brand-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-100 dark:border-gray-700/60 pt-3">
                                <a href="{{ route('system-tables.roles') }}" class="inline-flex items-center text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                    <span>{{ __('Details') }}</span> &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Activity Log Card -->
                        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700/60 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Audit Activities') }}</p>
                                    <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['activities_count']) }}</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['notifications_count'] }} {{ __('Notifications') }}</p>
                                </div>
                                <div class="rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 p-3 text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-100 dark:border-gray-700/60 pt-3">
                                <a href="{{ route('system-tables.activity-log') }}" class="inline-flex items-center text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                    <span>{{ __('Details') }}</span> &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Queues & Background Card -->
                        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-gray-100 dark:border-gray-700/60 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Pending Jobs') }}</p>
                                    <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending_jobs_count']) }}</h3>
                                    <p class="mt-1 text-xs {{ $stats['failed_jobs_count'] > 0 ? 'text-rose-600 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $stats['failed_jobs_count'] }} {{ __('Failed Jobs') }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-amber-500/10 dark:bg-amber-500/20 p-3 text-amber-600 dark:text-amber-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-100 dark:border-gray-700/60 pt-3">
                                <a href="{{ route('system-tables.queues') }}" class="inline-flex items-center text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                    <span>{{ __('Details') }}</span> &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Table Matrix Summary & Recent Activity -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                        <!-- Database Schema Checklist -->
                        <div class="xl:col-span-1 rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 5h16M9 4v16"></path></svg>
                                <span>{{ __('Database Tables Snapshot') }}</span>
                            </h3>
                            <div class="space-y-3">
                                @php
                                $schemaItems = [
                                    ['name' => 'users', 'count' => $stats['users_count'], 'route' => route('system-tables.users')],
                                    ['name' => 'sessions', 'count' => $stats['sessions_count'], 'route' => route('system-tables.users')],
                                    ['name' => 'roles', 'count' => $stats['roles_count'], 'route' => route('system-tables.roles')],
                                    ['name' => 'permissions', 'count' => $stats['permissions_count'], 'route' => route('system-tables.roles')],
                                    ['name' => 'activity_log', 'count' => $stats['activities_count'], 'route' => route('system-tables.activity-log')],
                                    ['name' => 'notifications', 'count' => $stats['notifications_count'], 'route' => route('system-tables.notifications')],
                                    ['name' => 'jobs', 'count' => $stats['pending_jobs_count'], 'route' => route('system-tables.queues')],
                                    ['name' => 'failed_jobs', 'count' => $stats['failed_jobs_count'], 'route' => route('system-tables.queues')],
                                    ['name' => 'job_batches', 'count' => $stats['job_batches_count'], 'route' => route('system-tables.queues')],
                                    ['name' => 'cache', 'count' => $stats['cache_count'], 'route' => route('system-tables.cache')],
                                    ['name' => 'cache_locks', 'count' => $stats['cache_locks_count'], 'route' => route('system-tables.cache')],
                                    ['name' => 'password_reset_tokens', 'count' => $stats['password_resets_count'], 'route' => route('system-tables.users')],
                                ];
                                @endphp
                                @foreach($schemaItems as $item)
                                    <a href="{{ $item['route'] }}" class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 hover:bg-brand-600/10 dark:hover:bg-brand-600/15 transition-colors group">
                                        <span class="font-mono text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-400">
                                            {{ $item['name'] }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                            {{ $item['count'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Recent Activities Stream -->
                        <div class="xl:col-span-2 rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>{{ __('Recent System Activities') }}</span>
                                </h3>
                                <a href="{{ route('system-tables.activity-log') }}" class="text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                    {{ __('Activity Log') }} &rarr;
                                </a>
                            </div>

                            @if($recentActivities->isEmpty())
                                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('No records found.') }}
                                </div>
                            @else
                                <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($recentActivities as $activity)
                                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                            <div class="flex items-start gap-3">
                                                @php
                                                    $eventVariant = match($activity->event) {
                                                        'created' => 'success',
                                                        'updated' => 'warning',
                                                        'deleted' => 'danger',
                                                        default => 'info',
                                                    };
                                                @endphp
                                                <x-badge :variant="$eventVariant" class="uppercase">
                                                    {{ $activity->event ?? 'event' }}
                                                </x-badge>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $activity->translated_description ?? $activity->description }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        <span class="font-mono">{{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}</span>
                                                        @if($activity->causer)
                                                            &bull; {{ __('Causer') }}: <span class="font-semibold">{{ $activity->causer->name ?? $activity->causer_id }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                                {{ $activity->created_at?->diffForHumans() }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
