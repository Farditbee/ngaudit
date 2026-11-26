@props([
    'searchPlaceholder' => 'Cari data...',
    'emptyMessage' => 'Tidak ada data yang ditemukan.',
    'fixed' => false,
])

<div class="rounded-xl bg-white shadow-sm ring-1 ring-neutral-900/10 dark:bg-neutral-950 dark:ring-white/10">
    @isset($filters)
        <div class="rounded-t-xl border-b border-neutral-200 bg-neutral-50/40 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
            {{ $filters }}
        </div>
    @endisset

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
        <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
            <span>Tampilkan</span>
            <select
                x-model.number="perPage"
                x-on:change="onPerPageChange()"
                class="h-9 rounded-md border border-neutral-200 bg-white py-1 pl-3 pr-8 text-sm text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-white/20"
            >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>data</span>
        </div>

        <div class="relative w-full sm:w-72">
            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
            <input
                type="search"
                x-model="search"
                x-on:input.debounce.350ms="onSearchInput()"
                placeholder="{{ $searchPlaceholder }}"
                class="block h-9 w-full rounded-md border border-neutral-200 bg-white pl-9 pr-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-white/20"
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="relative {{ $fixed ? 'overflow-x-hidden' : 'overflow-x-auto' }}">
        <div
            x-show="loading"
            x-cloak
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-[1px] dark:bg-neutral-950/60"
        >
            <div class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3.5 py-2 text-xs font-medium shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <x-lucide-loader-circle class="h-4 w-4 animate-spin" />
                <span>Memuat data...</span>
            </div>
        </div>

        <table class="{{ $fixed ? 'table-fixed w-full' : 'min-w-max w-full' }} border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-neutral-200 bg-neutral-50/70 text-xs uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400">
                    <template x-for="(col, index) in columns" :key="col.name">
                        <th
                            class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 font-medium select-none last:border-r-0 dark:border-neutral-800"
                            :class="[
                                col.sortable ? 'cursor-pointer hover:bg-neutral-100/80 dark:hover:bg-neutral-800/60' : '',
                                col.width || '',
                                col.align === 'center' ? 'text-center' : ''
                            ]"
                            x-on:click="toggleSort(index)"
                        >
                            <div class="flex items-center justify-between gap-2" :class="col.align === 'center' ? 'justify-center' : ''">
                                <span x-text="col.label"></span>
                                <template x-if="col.sortable">
                                    <span class="inline-flex flex-col text-neutral-400">
                                        <x-lucide-chevrons-up-down x-show="sortCol !== index" class="h-3.5 w-3.5 opacity-60" />
                                        <x-lucide-chevron-up x-show="sortCol === index && sortDir === 'asc'" x-cloak class="h-3.5 w-3.5 text-neutral-900 dark:text-neutral-100" />
                                        <x-lucide-chevron-down x-show="sortCol === index && sortDir === 'desc'" x-cloak class="h-3.5 w-3.5 text-neutral-900 dark:text-neutral-100" />
                                    </span>
                                </template>
                            </div>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <template x-if="!loading && rows.length === 0">
                    <tr>
                        <td :colspan="columns.length" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                </template>

                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Footer Pagination --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-neutral-200 px-6 py-4 text-sm dark:border-neutral-800">
        <p class="text-neutral-500 dark:text-neutral-400">
            Menampilkan
            <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="startEntry().toLocaleString('id-ID')"></span>
            sampai
            <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="endEntry().toLocaleString('id-ID')"></span>
            dari
            <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="filtered.toLocaleString('id-ID')"></span>
            data
            <template x-if="filtered !== total">
                <span>(disaring dari <span x-text="total.toLocaleString('id-ID')"></span> total data)</span>
            </template>
        </p>

        <div class="flex items-center gap-1">
            <button
                type="button"
                x-on:click="goToPage(page - 1)"
                x-bind:disabled="page <= 1 || loading"
                class="flex h-8 items-center gap-1 rounded-md px-2.5 text-sm hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
            >
                <x-lucide-chevron-left class="h-4 w-4" />
                <span>Sebelumnya</span>
            </button>

            <template x-for="(p, i) in visiblePages()" :key="p + '-' + i">
                <button
                    type="button"
                    x-on:click="goToPage(p)"
                    x-bind:disabled="p === '...' || loading"
                    x-text="p"
                    class="h-8 min-w-8 rounded-md px-2 text-sm"
                    x-bind:class="page === p
                        ? 'border border-neutral-200 bg-neutral-100 font-medium text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'
                        : (p === '...' ? 'cursor-default text-neutral-400' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800')"
                ></button>
            </template>

            <button
                type="button"
                x-on:click="goToPage(page + 1)"
                x-bind:disabled="page >= totalPages() || loading"
                class="flex h-8 items-center gap-1 rounded-md px-2.5 text-sm hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
            >
                <span>Selanjutnya</span>
                <x-lucide-chevron-right class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>
