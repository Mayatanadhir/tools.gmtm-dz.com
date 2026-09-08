@props(['alt' => config('app.name', 'ENGI-MATE')])

<picture {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="{{ $alt }}" class="block dark:hidden h-full w-auto object-contain">
    <img src="{{ asset('images/logo-dark.png') }}" alt="{{ $alt }}" class="hidden dark:block h-full w-auto object-contain">
</picture>


