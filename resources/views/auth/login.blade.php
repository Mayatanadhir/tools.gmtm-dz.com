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

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1.5">
                <x-text-input id="password" class="block w-full pe-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button"
                        id="toggle-password"
                        onclick="togglePassword()"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none focus:text-brand-600 transition-colors"
                        title="{{ __('Show password') }}"
                        aria-label="{{ __('Show password') }}">
                    {{-- Eye open icon --}}
                    <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{-- Eye slash icon --}}
                    <svg id="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.53 6.533A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.293 5.292M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <script>
            function togglePassword() {
                const input   = document.getElementById('password');
                const eyeOpen  = document.getElementById('icon-eye');
                const eyeSlash = document.getElementById('icon-eye-slash');
                const isHidden = input.type === 'password';

                input.type     = isHidden ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', isHidden);
                eyeSlash.classList.toggle('hidden', !isHidden);
            }
        </script>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-brand-600 shadow-sm focus:ring-brand-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 select-none">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 underline underline-offset-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600 transition" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @else
                <span></span>
            @endif

            <x-primary-button class="px-6 py-2.5">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register') && is_registration_open())
            <div class="text-center text-sm text-gray-600 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-4 mt-6">
                <span>{{ __("Don't have an account?") }}</span>
                <a href="{{ route('register') }}" class="underline font-semibold text-brand-700 dark:text-brand-400 hover:text-brand-600 ms-1">
                    {{ __('Create an account') }}
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
