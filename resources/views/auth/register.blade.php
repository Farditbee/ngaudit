<x-guest-layout>
    <div class="flex flex-col gap-6">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <x-application-logo class="h-9 w-9" />
            <span class="text-xl font-bold">{{ config('app.name') }}</span>
        </a>

        <div>
            <h1 class="mb-2 text-2xl font-semibold">{{ __('Sign Up to :app', ['app' => config('app.name')]) }}</h1>
            <p class="text-base text-neutral-500 dark:text-neutral-400">{{ __('Ship Faster and Focus on Growth.') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ showPassword: false, showConfirmation: false }">
            @csrf

            <div class="space-y-2">
                <label for="name" class="block text-sm font-medium leading-5">{{ __('Username') }}*</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    autocomplete="name" placeholder="{{ __('Enter your username') }}" class="block h-10 w-full rounded-md dark:bg-neutral-950 dark:text-neutral-100 border-neutral-200 dark:border-neutral-800 px-3 text-sm shadow-xs focus:border-neutral-900 dark:focus:border-neutral-100 focus:ring-2 focus:ring-neutral-900/20 dark:focus:ring-white/20" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium leading-5">{{ __('Email address') }}*</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    autocomplete="username" placeholder="{{ __('Enter your email address') }}" class="block h-10 w-full rounded-md dark:bg-neutral-950 dark:text-neutral-100 border-neutral-200 dark:border-neutral-800 px-3 text-sm shadow-xs focus:border-neutral-900 dark:focus:border-neutral-100 focus:ring-2 focus:ring-neutral-900/20 dark:focus:ring-white/20" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium leading-5">{{ __('Password') }}*</label>
                <div class="relative">
                    <input id="password" name="password" required autocomplete="new-password"
                        x-bind:type="showPassword ? 'text' : 'password'" placeholder="••••••••••••"
                        class="block h-10 w-full rounded-md dark:bg-neutral-950 dark:text-neutral-100 border-neutral-200 dark:border-neutral-800 px-3 text-sm shadow-xs focus:border-neutral-900 dark:focus:border-neutral-100 focus:ring-2 focus:ring-neutral-900/20 dark:focus:ring-white/20 pe-10" />
                    <button type="button" x-on:click="showPassword = !showPassword"
                        class="absolute inset-y-0 end-0 flex w-10 items-center justify-center text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100"
                        x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 6.1A9.8 9.8 0 0112 6c6.5 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.6A16.6 16.6 0 002 12s3.5 7 10 7a9.7 9.7 0 004.4-1"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium leading-5">{{ __('Confirm Password') }}*</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                        x-bind:type="showConfirmation ? 'text' : 'password'" placeholder="••••••••••••"
                        class="block h-10 w-full rounded-md dark:bg-neutral-950 dark:text-neutral-100 border-neutral-200 dark:border-neutral-800 px-3 text-sm shadow-xs focus:border-neutral-900 dark:focus:border-neutral-100 focus:ring-2 focus:ring-neutral-900/20 dark:focus:ring-white/20 pe-10" />
                    <button type="button" x-on:click="showConfirmation = !showConfirmation"
                        class="absolute inset-y-0 end-0 flex w-10 items-center justify-center text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100"
                        x-bind:aria-label="showConfirmation ? 'Hide password' : 'Show password'">
                        <svg x-show="!showConfirmation" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showConfirmation" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 6.1A9.8 9.8 0 0112 6c6.5 0 10 6 10 6a17 17 0 01-3.2 3.9M6.6 6.6A16.6 16.6 0 002 12s3.5 7 10 7a9.7 9.7 0 004.4-1"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>

            <label for="privacy_policy" class="flex items-center gap-2">
                <input id="privacy_policy" type="checkbox" required
                    class="h-4 w-4 rounded border-neutral-300 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-neutral-900/30 dark:focus:ring-white/30">
                <span class="text-sm"><span class="text-neutral-500 dark:text-neutral-400">{{ __('I agree to') }}</span> <a href="#" class="hover:underline">{{ __('privacy policy & terms') }}</a></span>
            </label>

            <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center rounded-md bg-neutral-900 dark:bg-neutral-100 px-4 text-sm font-medium text-white dark:text-neutral-900 transition hover:bg-neutral-800 dark:hover:bg-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-900/40 dark:focus:ring-white/40 focus:ring-offset-2">
                {{ __('Sign Up to :app', ['app' => config('app.name')]) }}
            </button>

            <p class="text-center text-neutral-500 dark:text-neutral-400">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="text-neutral-900 dark:text-neutral-100 hover:underline">{{ __('Sign in instead') }}</a>
            </p>

            </form>
    </div>
</x-guest-layout>