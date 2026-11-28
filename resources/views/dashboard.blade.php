<x-admin-layout :breadcrumbs="['Dashboard']">
    <div class="space-y-5">
        <div>
            <h1 class="text-base font-bold text-neutral-800 dark:text-neutral-100">Dashboard</h1>
        </div>

        {{-- Welcome Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
            <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200 sm:text-base">
                Selamat Datang, {{ auth()->user()?->name ?? 'Pengguna' }}!
            </p>
        </div>

        {{-- Papan Pengumuman Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
            <h2 class="mb-6 text-center text-lg font-bold text-neutral-800 dark:text-neutral-100">
                Papan Pengumuman
            </h2>

            {{-- Warning Alert Banner --}}
            <div
                x-data="{ showNotice: true }"
                x-show="showNotice"
                class="relative rounded-xl border border-amber-200/90 border-l-[6px] border-l-[#ffc107] bg-[#fff3cd] p-5 text-amber-950 shadow-2xs transition-all dark:border-amber-800/80 dark:border-l-amber-500 dark:bg-amber-950/40 dark:text-amber-200"
            >
                {{-- Dismiss Button (X) --}}
                <button
                    type="button"
                    x-on:click="showNotice = false"
                    title="Tutup Pengumuman"
                    class="absolute right-3.5 top-3.5 rounded p-1 text-amber-800/60 transition-colors hover:bg-amber-200/60 hover:text-amber-950 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-100"
                >
                    <x-lucide-x class="h-4 w-4" />
                </button>

                <div class="pr-7">
                    <h3 class="text-sm font-semibold text-amber-950 dark:text-amber-100">
                        Audit {{ date('Y') }}
                    </h3>
                    <p class="mt-1 text-xs leading-relaxed text-amber-900/90 dark:text-amber-200/90 sm:text-sm">
                        Untuk menginput audit tahun {{ date('Y') }}, silahkan gunakan menu Audit {{ date('Y') }} pada menu kiri bagian bawah.
                    </p>
                </div>

                <hr class="my-4 border-amber-300/80 dark:border-amber-800/60" />

                <div class="flex flex-wrap items-center gap-2.5">
                    <a
                        href="{{ route('audit.sprin.index') }}"
                        class="inline-flex items-center justify-center rounded-md bg-[#f59e0b] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-[#d97706] focus:outline-none"
                    >
                        Input Sprin {{ date('Y') }}
                    </a>

                    <a
                        href="{{ route('audit.pka.index') }}"
                        class="inline-flex items-center justify-center rounded-md bg-[#64748b] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-[#475569] focus:outline-none"
                    >
                        Input PKA {{ date('Y') }}
                    </a>

                    <a
                        href="{{ route('audit.kka.index') }}"
                        class="inline-flex items-center justify-center rounded-md bg-[#0d6efd] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-[#0b5ed7] focus:outline-none"
                    >
                        Input KKA {{ date('Y') }}
                    </a>

                    <a
                        href="{{ route('audit.tindak-lanjut.index') }}"
                        class="inline-flex items-center justify-center rounded-md bg-[#dc3545] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-[#bb2d3b] focus:outline-none"
                    >
                        Tinjut Auditi {{ date('Y') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
