@props([
    'variant' => 'success',
    'dismissible' => true,
    'title' => null,
])

@php
    $normalizedVariant = match($variant) {
        'brand', 'orange', 'primary' => 'primary',
        'success', 'emerald', 'green' => 'success',
        'danger', 'rose', 'red' => 'danger',
        'warning', 'amber', 'yellow' => 'warning',
        'info', 'indigo', 'blue' => 'info',
        default => 'neutral',
    };

    $containerClasses = match($normalizedVariant) {
        'primary' => 'bg-orange-50 dark:bg-orange-950/30 border-orange-200 dark:border-orange-800/80 text-orange-900 dark:text-orange-200',
        'success' => 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/80 text-emerald-900 dark:text-emerald-200',
        'danger' => 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/80 text-rose-900 dark:text-rose-200',
        'warning' => 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/80 text-amber-900 dark:text-amber-200',
        'info' => 'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800/80 text-indigo-900 dark:text-indigo-200',
        'neutral' => 'bg-gray-50 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-200',
    };

    $iconClasses = match($normalizedVariant) {
        'primary' => 'text-orange-500 dark:text-orange-400',
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'danger' => 'text-rose-600 dark:text-rose-400',
        'warning' => 'text-amber-500 dark:text-amber-400',
        'info' => 'text-indigo-600 dark:text-indigo-400',
        'neutral' => 'text-gray-500 dark:text-gray-400',
    };

    $closeHoverClasses = match($normalizedVariant) {
        'primary' => 'hover:bg-orange-100 dark:hover:bg-orange-900/50 text-orange-600 dark:text-orange-300',
        'success' => 'hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300',
        'danger' => 'hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-300',
        'warning' => 'hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-300',
        'info' => 'hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300',
        'neutral' => 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300',
    };
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     {{ $attributes->merge(['class' => "p-4 rounded-xl border shadow-sm text-sm flex items-start sm:items-center justify-between gap-3 $containerClasses"]) }}
     role="alert">
    <div class="flex items-start sm:items-center gap-3">
        <div class="shrink-0 mt-0.5 sm:mt-0">
            @if($normalizedVariant === 'success')
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            @elseif($normalizedVariant === 'danger')
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            @elseif($normalizedVariant === 'warning')
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            @elseif($normalizedVariant === 'info')
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            @elseif($normalizedVariant === 'primary')
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
            @else
                <svg class="w-5 h-5 {{ $iconClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            @endif
        </div>

        <div>
            @if($title)
                <div class="font-bold text-sm mb-0.5 leading-snug">{{ $title }}</div>
            @endif
            <div class="leading-relaxed">{{ $slot }}</div>
        </div>
    </div>

    @if($dismissible)
        <button type="button"
                @click="show = false"
                class="p-1.5 rounded-lg transition-colors shrink-0 {{ $closeHoverClasses }}"
                aria-label="{{ __('Close') }}">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
