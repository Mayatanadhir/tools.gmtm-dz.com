<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
        <div class="mb-4">
            <x-alert variant="danger" :dismissible="true">
                {{ session('error') }}
            </x-alert>
        </div>
    @elseif ($errors->has('registration_closed'))
        <div class="mb-4">
            <x-alert variant="danger" :dismissible="true">
                {{ $errors->first('registration_closed') }}
            </x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register') && is_registration_open())
            <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-4">
                <span>{{ __("Don't have an account?") }}</span>
                <a href="{{ route('register') }}" class="underline font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-500 ms-1">
                    {{ __('Create an account') }}
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
