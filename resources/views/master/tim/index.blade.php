<x-admin-layout :breadcrumbs="['Data Master', 'Tim']">
    <div class="space-y-6" x-data="timDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data Tim</h1>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Tim</span>
            </button>
        </div>

        {{-- Flash Alert --}}
        <div
            x-show="flashMessage !== ''"
            x-cloak
            x-transition.opacity
            class="flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200"
        >
            <div class="flex items-center gap-2">
                <x-lucide-circle-check class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <span x-text="flashMessage"></span>
            </div>
            <button type="button" x-on:click="flashMessage = ''" class="text-emerald-700 hover:text-emerald-950 dark:text-emerald-300">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari nama tim, Daltu, Dalnis, Ketua Tim, atau Anggota..."
            emptyMessage="Tidak ada data tim yang ditemukan."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Lingkup"
                        placeholder="Semua Lingkup"
                        itemsExpr="lingkupOptions"
                        valueModel="filterLingkup"
                        changeAction="onFilterChange()"
                    />
                </div>

                <div class="mt-3 flex justify-end" x-show="filterLingkup !== ''" x-cloak>
                    <button
                        type="button"
                        x-on:click="resetFilters()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-3 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        <x-lucide-rotate-ccw class="h-3.5 w-3.5" />
                        <span>Reset Filter</span>
                    </button>
                </div>
            </x-slot:filters>

            <template x-for="(row, rIndex) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                    <td class="max-w-[12rem] border-r border-neutral-200 px-4 py-3 font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                        <div class="break-words" x-text="row.nama_tim"></div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <div class="flex flex-col items-start gap-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="row.lingkup === 'Nasional'
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                x-text="row.lingkup"
                            ></span>
                            <template x-if="row.lingkup === 'Nasional' && row.plot_name">
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[11px] font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300" x-text="row.plot_name"></span>
                            </template>
                            <template x-if="row.lingkup === 'Wilayah' && row.province_name">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300" x-text="row.province_name"></span>
                            </template>
                        </div>
                    </td>
                    <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <div class="space-y-1.5 text-xs">
                            <div>
                                <span class="font-medium text-neutral-500 dark:text-neutral-400">Daltu:</span>
                                <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="row.daltu_name || '-'"></span>
                            </div>
                            <div>
                                <span class="font-medium text-neutral-500 dark:text-neutral-400">Dalnis:</span>
                                <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="row.dalnis_name || '-'"></span>
                            </div>
                        </div>
                    </td>
                    <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="row.ketua_tim_name || '-'"></span>
                    </td>
                    <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <span class="inline-flex items-center rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-semibold text-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                            <span x-text="row.jumlah_anggota ?? 0"></span>&nbsp;Orang
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="syncTim(row)"
                                x-bind:disabled="syncingId === row.id"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border transition-colors disabled:opacity-50"
                                :class="row.needs_sync
                                    ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/50 dark:text-amber-300 dark:hover:bg-amber-900/50'
                                    : 'border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-50 dark:border-emerald-900/50 dark:bg-neutral-900 dark:text-emerald-400 dark:hover:bg-emerald-950/40'"
                                title="Sinkronkan Anggota"
                            >
                                <x-lucide-refresh-cw class="h-3.5 w-3.5" x-bind:class="syncingId === row.id ? 'animate-spin' : ''" />
                            </button>
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit Tim"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Tim"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        <x-master.tim-modals />
    </div>
</x-admin-layout>
