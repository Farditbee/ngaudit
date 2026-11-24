@props(['breadcrumbs' => []])

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
    <body class="bg-white dark:bg-neutral-950 font-sans text-neutral-900 dark:text-neutral-100 antialiased" x-data="{ mobileOpen: false, collapsed: false }">
        <div x-show="mobileOpen" x-cloak x-on:click="mobileOpen = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

        <x-admin.sidebar />

        <div class="flex min-h-screen flex-col transition-[padding] duration-200" x-bind:class="collapsed ? 'lg:pl-12' : 'lg:pl-64'">
            <x-admin.header :breadcrumbs="$breadcrumbs" />

            <main class="mx-auto w-full max-w-[90rem] flex-1 px-4 py-6 sm:px-6">
                {{ $slot }}
            </main>

            <footer class="border-t border-neutral-200 dark:border-neutral-800 px-4 py-4 text-sm text-neutral-500 dark:text-neutral-400 sm:px-6">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </footer>
        </div>
    </body>
</html>
