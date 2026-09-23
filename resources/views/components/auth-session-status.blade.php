@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 rounded-lg border px-4 py-3 text-sm border-green-200 dark:border-green-800/30 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200']) }}>
        {{ $status }}
    </div>
@endif
