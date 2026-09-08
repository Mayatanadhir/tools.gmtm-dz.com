@props([
    'header' => null,
    'toolbar' => null,
    'pagination' => null,
])

<div {{ $attributes->merge(['class' => 'w-full overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800 shadow-sm']) }}>
    @if($toolbar)
        <div class="p-5 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            {{ $toolbar }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-start text-xs text-gray-600 dark:text-gray-300">
            @if($header)
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 uppercase font-semibold text-start border-b border-gray-100 dark:border-gray-700/60">
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($pagination)
        <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
            {{ $pagination }}
        </div>
    @endif
</div>
