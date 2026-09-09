<x-guest-layout>
    <div class="mb-5 text-center">
        <div class="flex justify-center mb-2">
            <x-badge variant="primary" :dot="true" :dot-ping="true">
                {{ __('Initial System Setup') }}
            </x-badge>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Create Super Admin Account') }}
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ __('No administrator account exists yet. Please set up the first Super Admin account to initialize the system.') }}
        </p>
    </div>

    <!-- Small Specification Table -->
    <div class="mb-5 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/40">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700/80 text-xs">
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                <tr>
                    <td class="px-3.5 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ __('Assigned Role') }}
                    </td>
                    <td class="px-3.5 py-2 text-end">
                        <x-badge variant="warning">
                            <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ __('Super-Admin') }}
                        </x-badge>
                    </td>
                </tr>
                <tr>
                    <td class="px-3.5 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ __('Initial Status') }}
                    </td>
                    <td class="px-3.5 py-2 text-end">
                        <x-badge variant="success" :dot="true">
                            {{ __('Active') }}
                        </x-badge>
                    </td>
                </tr>
                <tr>
                    <td class="px-3.5 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ __('Permissions Scope') }}
                    </td>
                    <td class="px-3.5 py-2 text-end">
                        <x-badge variant="info">
                            <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            {{ __('Full System Authority') }}
                        </x-badge>
                    </td>
                <tr>
                    <td class="px-3.5 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ __('Database Schema') }}
                    </td>
                    <td class="px-3.5 py-2 text-end">
                        @if ($needsMigration ?? false)
                            <x-badge variant="warning">
                                <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ __('Auto-Migration Required') }}
                            </x-badge>
                        @else
                            <x-badge variant="success" :dot="true">
                                {{ __('Schema Ready') }}
                            </x-badge>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($needsMigration ?? false)
        <div class="mb-5 p-3 rounded-lg bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ __('Database tables will be built and initialized automatically upon launch.') }}</span>
        </div>
    @endif

    <!-- Onboarding Form -->
    <form method="POST" action="{{ route('system-tables.setup.store') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Super Admin Name')" />
            <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name', 'Super Admin')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Super Admin Email')" />
            <x-text-input id="email" class="block mt-1 w-full text-sm" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Super Admin Password')" />
            <x-text-input id="password" class="block mt-1 w-full text-sm" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Super Admin Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full text-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col gap-3">
            <x-primary-button class="w-full justify-center py-2.5 text-sm gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>{{ __('Initialize & Launch System') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
