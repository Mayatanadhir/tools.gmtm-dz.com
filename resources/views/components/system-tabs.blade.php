@props(['active' => 'index'])

@php
$tabs = [
    'index' => [
        'name' => __('Overview'),
        'route' => route('system-tables.index'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>',
    ],
    'users' => [
        'name' => __('Users & Sessions'),
        'route' => route('system-tables.users'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
    ],
    'roles' => [
        'name' => __('Roles & Permissions'),
        'route' => route('system-tables.roles'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
    ],
    'activity-log' => [
        'name' => __('Activity Log'),
        'route' => route('system-tables.activity-log'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    ],
    'notifications' => [
        'name' => __('Notifications'),
        'route' => route('system-tables.notifications'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>',
    ],
    'queues' => [
        'name' => __('Queues & Jobs'),
        'route' => route('system-tables.queues'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
    ],
    'cache' => [
        'name' => __('Cache & Locks'),
        'route' => route('system-tables.cache'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 5h16M9 4v16"></path></svg>',
    ],
    'pruning' => [
        'name' => __('Data Pruning'),
        'route' => route('system-tables.pruning'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
    ],
    'backups' => [
        'name' => __('Database Backups'),
        'route' => route('system-tables.backups'),
        'icon' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>',
    ],
];
@endphp

<div class="rounded-2xl bg-white dark:bg-gray-800 p-3.5 shadow-sm border border-gray-100 dark:border-gray-700/60 sticky top-24">
    <div class="px-3 py-2 mb-2 border-b border-gray-100 dark:border-gray-700/60">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
            {{ __('Navigation') }}
        </p>
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
            {{ __('System Tables') }}
        </h3>
    </div>

    <nav class="space-y-1.5" aria-label="System Tables Sidebar">
        @foreach($tabs as $key => $tab)
            @php
                $isActive = ($active === $key);
            @endphp
            <a href="{{ $tab['route'] }}"
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 {{ $isActive ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25 dark:bg-orange-600 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700/50' }}">
                <span class="shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'text-white' : 'text-gray-400 dark:text-gray-500 group-hover:text-orange-500 dark:group-hover:text-orange-400' }}">
                    {!! $tab['icon'] !!}
                </span>
                <span class="truncate">{{ $tab['name'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 px-3">
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>{{ __('Status') }}</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('Active') }}
            </span>
        </div>
    </div>
</div>
