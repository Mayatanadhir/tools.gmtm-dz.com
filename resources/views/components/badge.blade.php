@props([
    'variant' => 'neutral',
    'size' => 'sm',
    'dot' => false,
    'dotPing' => false,
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

    $variantClasses = match($normalizedVariant) {
        'primary' => 'bg-orange-500/10 text-orange-700 dark:text-orange-300 border-orange-500/20',
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20',
        'danger' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20',
        'warning' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20',
        'info' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20',
        'neutral' => 'bg-gray-500/10 text-gray-700 dark:text-gray-300 border-gray-500/20 dark:border-gray-600/30',
    };

    $dotColors = match($normalizedVariant) {
        'primary' => 'bg-orange-500',
        'success' => 'bg-emerald-500',
        'danger' => 'bg-rose-500',
        'warning' => 'bg-amber-500',
        'info' => 'bg-indigo-500',
        'neutral' => 'bg-gray-400 dark:bg-gray-500',
    };

    $sizeClasses = match($size) {
        'md' => 'px-3 py-1 text-xs',
        default => 'px-2.5 py-0.5 text-xs',
    };

    $dotSizeClass = match($size) {
        'md' => 'w-2 h-2',
        default => 'w-1.5 h-1.5',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full font-semibold border $sizeClasses $variantClasses"]) }}>
    @if($dot)
        @if($dotPing)
            <span class="relative flex {{ $dotSizeClass }} shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $dotColors }}"></span>
                <span class="relative inline-flex rounded-full {{ $dotSizeClass }} {{ $dotColors }}"></span>
            </span>
        @else
            <span class="{{ $dotSizeClass }} rounded-full {{ $dotColors }} shrink-0"></span>
        @endif
    @endif
    {{ $slot }}
</span>
