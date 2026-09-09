@props(['status'])

@if ($status)
    <x-alert variant="success" {{ $attributes }}>
        {{ $status }}
    </x-alert>
@endif
