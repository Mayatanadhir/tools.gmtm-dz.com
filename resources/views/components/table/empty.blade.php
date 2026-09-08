@props([
    'colspan' => 1,
    'message' => null,
])

<tr>
    <td colspan="{{ $colspan }}" {{ $attributes->merge(['class' => 'px-5 py-10 text-center text-gray-500 dark:text-gray-400']) }}>
        <div class="flex flex-col items-center justify-center space-y-2">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-xs font-medium">{{ $message ?? __('No records found.') }}</p>
        </div>
    </td>
</tr>
