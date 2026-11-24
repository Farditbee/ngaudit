@props(['breadcrumbs' => []])

<header class="sticky top-0 z-30 border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950">
    <div class="flex w-full items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <button type="button" class="rounded-md p-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                x-on:click="window.innerWidth >= 1024 ? collapsed = !collapsed : mobileOpen = !mobileOpen">
                <x-lucide-panel-left class="h-5 w-5" />
                <span class="sr-only">Toggle sidebar</span>
            </button>
            <div class="hidden h-4 w-px bg-neutral-200 sm:block"></div>
            <nav class="hidden items-center gap-2 text-sm sm:flex" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $crumb)
                    @if ($loop->last)
                        <span class="font-normal text-neutral-900 dark:text-neutral-100">{{ $crumb }}</span>
                    @else
                        <span class="text-neutral-500 dark:text-neutral-400">{{ $crumb }}</span>
                        <x-lucide-chevron-right class="h-3.5 w-3.5 text-neutral-500 dark:text-neutral-400" />
                    @endif
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-1.5">
            @if (session()->has('impersonator_id'))
                <form method="POST" action="{{ route('master.user.leave-impersonate') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-900/60"
                    >
                        <x-lucide-log-out class="h-3.5 w-3.5" />
                        <span>Kembali ke Akun Utama</span>
                    </button>
                </form>
            @endif
            <div class="hidden items-center overflow-hidden rounded-md border border-neutral-200 dark:border-neutral-800 text-xs font-semibold md:flex">
                <span class="flex items-center gap-1.5 bg-neutral-50 dark:bg-neutral-900 px-2.5 py-1.5"><x-lucide-star class="h-3.5 w-3.5" /> Star</span>
                <span class="border-s border-neutral-200 dark:border-neutral-800 px-2.5 py-1.5">152</span>
            </div>
            <button type="button" class="rounded-md p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                x-on:click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                x-bind:aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                <x-lucide-moon x-show="!dark" class="h-4 w-4" />
                <x-lucide-sun x-show="dark" x-cloak class="h-4 w-4" />
            </button>
            <button type="button" class="rounded-md p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800"><x-lucide-languages class="h-4 w-4" /><span class="sr-only">Language</span></button>

            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                <button type="button" x-on:click="open = !open" class="relative block rounded-full">
                    <img src="{{ asset('images/avatars/avatar-1.webp') }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover">
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-neutral-950 bg-green-500"></span>
                </button>
                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-72 rounded-xl border border-neutral-200 bg-white p-1.5 shadow-lg dark:border-neutral-800 dark:bg-neutral-950">
                    <div class="flex items-center gap-3 px-3 py-3">
                        <div class="relative shrink-0">
                            <img src="{{ asset('images/avatars/avatar-1.webp') }}" alt="{{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover">
                            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-green-500 dark:border-neutral-950"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold">{{ auth()->user()->name }}</p>
                            <p class="truncate text-sm text-neutral-500 dark:text-neutral-400">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="my-1 h-px bg-neutral-200 dark:bg-neutral-800"></div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <x-lucide-user class="h-4 w-4" /> {{ __('My Account') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <x-lucide-settings class="h-4 w-4" /> {{ __('Settings') }}
                    </a>
                    <div class="my-1 h-px bg-neutral-200 dark:bg-neutral-800"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40">
                            <x-lucide-log-out class="h-4 w-4" /> {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
