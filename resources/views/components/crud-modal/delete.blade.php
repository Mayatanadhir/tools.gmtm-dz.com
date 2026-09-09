@props([
    'show'        => 'showDeleteModal',
    'actionUrl'   => '',
    'title'       => null,
    'message'     => null,
    'itemName'    => null,
    'submitText'  => null,
])

@php
    $resolvedTitle      = $title      ?? __('Delete');
    $resolvedMessage    = $message    ?? __('Are you sure you want to permanently delete this record? This action cannot be undone.');
    $resolvedSubmitText = $submitText ?? __('Delete');
@endphp

{{--
    Unified Delete Confirmation Modal (x-crud-modal.delete)
    --------------------------------------------------------
    Usage:
        <x-crud-modal.delete
            show="showDeleteUserModal"
            :action-url="deleteUserActionUrl"
            :title="__('Delete User')"
            :message="__('This action cannot be undone.')"
            :item-name="deleteUserName"
            :submit-text="__('Delete User')"
        />

    The parent x-data scope must expose the Alpine.js variables referenced
    in :show (the modal flag), :action-url, and optionally :item-name.
--}}
<div
    x-cloak
    x-show="{{ $show }}"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="delete-modal-title-{{ Str::random(6) }}"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="{{ $show }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
        @click="{{ $show }} = false"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
            x-show="{{ $show }}"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700"
            @click.stop
        >
            <form method="POST" :action="{{ $actionUrl }}">
                @csrf
                @method('DELETE')

                {{-- Hidden fields slot (extra inputs if needed) --}}
                {{ $slot }}

                <div class="px-6 pt-6 pb-4">
                    <div class="flex items-start gap-4">
                        {{-- Danger Icon --}}
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white" id="delete-modal-heading">
                                {{ $resolvedTitle }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $resolvedMessage }}
                                @if($itemName !== null)
                                    <strong class="font-semibold text-gray-800 dark:text-gray-200" x-text="{{ $itemName }}"></strong>?
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 rounded-b-2xl">
                    <x-secondary-button type="button" @click="{{ $show }} = false">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button type="submit">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ $resolvedSubmitText }}
                    </x-danger-button>
                </div>
            </form>
        </div>
    </div>
</div>
