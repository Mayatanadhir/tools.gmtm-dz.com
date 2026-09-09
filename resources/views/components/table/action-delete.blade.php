@props([
    'actionUrl' => null,
    'confirmMessage' => null,
    'method' => 'DELETE',
])

@if($actionUrl)
    <form method="POST" action="{{ $actionUrl }}" class="inline" @if($confirmMessage) onsubmit="return confirm('{{ addslashes($confirmMessage) }}')" @endif>
        @csrf
        @method($method)
        <x-table.action type="delete" button-type="submit" {{ $attributes }}>
            {{ $slot }}
        </x-table.action>
    </form>
@else
    <x-table.action type="delete" {{ $attributes }}>
        {{ $slot }}
    </x-table.action>
@endif
