@props([
    'name',
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

@php
    $component = 'icons.tools.' . $name;
@endphp

@if(view()->exists('components.' . $component))
    <x-dynamic-component :component="$component" :class="$class" {{ $attributes }} />
@else
    {{-- Generic Tool Fallback Icon --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="10" y="10" width="44" height="44" rx="12" fill="#F1F5F9" stroke="#94A3B8" stroke-width="1.5" />
        <path d="M26 32 H38 M32 26 V38" stroke="#0284C7" stroke-width="2.5" stroke-linecap="round" />
    </svg>
@endif
