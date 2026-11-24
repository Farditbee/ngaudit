<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark')</script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-neutral-900 dark:text-neutral-100 antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-white dark:bg-neutral-950 px-4 py-10 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <img src="{{ asset('images/auth/auth-background-shape.svg') }}" alt="" class="max-w-none dark:invert" width="686" height="671">
            </div>

            <div class="relative z-10 w-full rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 py-6 px-6 shadow-sm sm:max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
