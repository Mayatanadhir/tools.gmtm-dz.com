@props(['alt' => config('app.name', 'ERP-GMTM')])

<picture {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center']) }}>
    <img src="{{ asset('images/Logo-black.png') }}" alt="{{ $alt }}" class="block dark:hidden h-full w-auto object-contain">
    <img src="{{ asset('images/Logo-white.png') }}" alt="{{ $alt }}" class="hidden dark:block h-full w-auto object-contain">
</picture>


