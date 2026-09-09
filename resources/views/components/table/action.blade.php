@props([
    'type' => 'view',
    'href' => null,
    'title' => null,
    'buttonType' => 'button',
])

@php
$resolvedTitle = $title ?? match($type) {
    'edit' => __('Edit'),
    'delete' => __('Delete'),
    'download' => __('Download'),
    'restore' => __('Restore'),
    'primary' => __('Action'),
    'success' => __('Approve'),
    default => __('View'),
};

$themeClasses = match($type) {
    'edit', 'restore' => 'bg-amber-50 text-amber-700 border-amber-200/80 hover:bg-amber-500 hover:text-white hover:border-amber-500 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60 dark:hover:bg-amber-500 dark:hover:text-white focus:ring-amber-500',
    'delete' => 'bg-rose-50 text-rose-700 border-rose-200/80 hover:bg-rose-600 hover:text-white hover:border-rose-600 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60 dark:hover:bg-rose-600 dark:hover:text-white focus:ring-rose-500',
    'primary' => 'bg-orange-50 text-orange-700 border-orange-200/80 hover:bg-orange-500 hover:text-white hover:border-orange-500 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800/60 dark:hover:bg-orange-500 dark:hover:text-white focus:ring-orange-500',
    'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60 dark:hover:bg-emerald-600 dark:hover:text-white focus:ring-emerald-500',
    default => 'bg-indigo-50 text-indigo-700 border-indigo-200/80 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60 dark:hover:bg-indigo-600 dark:hover:text-white focus:ring-indigo-500',
};

$hasSlot = $slot->isNotEmpty();
$sizeClasses = $hasSlot ? 'px-2.5 py-1 gap-1.5' : 'p-1.5';
$baseClasses = "inline-flex items-center justify-center font-medium text-xs border rounded-lg transition-all duration-150 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed";
@endphp

@if($href)
    <a href="{{ $href }}"
       title="{{ $resolvedTitle }}"
       {{ $attributes->merge(['class' => "$baseClasses $themeClasses $sizeClasses"]) }}>
        @if($type === 'edit')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        @elseif($type === 'delete')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        @elseif($type === 'download')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        @elseif($type === 'restore')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        @elseif($type === 'primary')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        @elseif($type === 'success')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        @else
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        @endif

        @if($hasSlot)
            <span>{{ $slot }}</span>
        @else
            <span class="sr-only">{{ $resolvedTitle }}</span>
        @endif
    </a>
@else
    <button type="{{ $buttonType }}"
            title="{{ $resolvedTitle }}"
            {{ $attributes->merge(['class' => "$baseClasses $themeClasses $sizeClasses"]) }}>
        @if($type === 'edit')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        @elseif($type === 'delete')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        @elseif($type === 'download')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        @elseif($type === 'restore')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        @elseif($type === 'primary')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        @elseif($type === 'success')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        @else
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        @endif

        @if($hasSlot)
            <span>{{ $slot }}</span>
        @else
            <span class="sr-only">{{ $resolvedTitle }}</span>
        @endif
    </button>
@endif
