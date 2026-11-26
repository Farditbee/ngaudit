<x-admin-layout :breadcrumbs="['Audit', 'Persiapan', 'Sprin']">
    <div class="space-y-6" x-data="sprinDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data Sprin</h1>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Sprin</span>
            </button>
        </div>


        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari nomor Sprin atau nama tim..."
            emptyMessage="Tidak ada data Sprin yang ditemukan."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Tahap"
                        placeholder="Semua Tahap"
                        itemsExpr="tahapOptions"
                        valueModel="filterTahap"
                        changeAction="onFilterChange()"
                    />
                </div>

                <div class="mt-3 flex justify-end" x-show="filterTahap !== ''" x-cloak>
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
                    <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <div class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="row.no_sprin"></div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center dark:border-neutral-800">
                        <div class="inline-flex flex-col items-center gap-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="row.sprin_tipe === 'Nasional'
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                x-text="row.sprin_tipe"
                            ></span>
                            <template x-if="row.sprin_tipe === 'Nasional' && row.plot_name">
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[11px] font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300" x-text="row.plot_name"></span>
                            </template>
                            <template x-if="row.sprin_tipe === 'Wilayah' && row.province_name">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300" x-text="row.province_name"></span>
                            </template>
                        </div>
                    </td>
                    <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <div class="break-words font-semibold text-neutral-900 dark:text-neutral-100" x-text="row.nama_tim"></div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300" x-text="row.tahap === 'Khusus' ? 'Khusus' : 'Tahap ' + row.tahap"></span>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                        <div class="inline-flex flex-col items-center rounded-md border border-neutral-200 bg-neutral-50/70 px-2 py-1 text-xs dark:border-neutral-800 dark:bg-neutral-900/50">
                            <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="formatDate(row.tgl_mulai)"></span>
                            <span class="text-[10px] font-medium text-neutral-400 dark:text-neutral-500">s/d</span>
                            <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="formatDate(row.tgl_akhir_value)"></span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                        <div class="inline-flex flex-col items-center gap-1">
                            {{-- Jika Super Admin / Daltu dan belum ada reviu: tampilkan tombol Reviu warna merah saja --}}
                            <template x-if="row.can_review && !row.latest_review_status">
                                <button
                                    type="button"
                                    x-on:click="openReviewModal(row)"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
                                    title="Belum direviu - Klik untuk melakukan reviu"
                                >
                                    <x-lucide-clipboard-check class="h-3.5 w-3.5" />
                                    <span>Reviu</span>
                                </button>
                            </template>

                            {{-- Jika bukan reviewer dan belum ada reviu: tampilkan badge Menunggu (dapat diklik untuk melihat lembar reviu) --}}
                            <template x-if="!row.can_review && !row.latest_review_status">
                                <button
                                    type="button"
                                    x-on:click="openReviewModal(row)"
                                    class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-600 transition-colors hover:bg-neutral-200 focus:outline-none dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700"
                                    title="Klik untuk melihat lembar reviu"
                                >
                                    Menunggu
                                </button>
                            </template>

                            {{-- Jika sudah direviu (Disetujui / Koreksi): SEMUA AKUN dapat melihat hasil reviu --}}
                            <template x-if="row.latest_review_status">
                                <button
                                    type="button"
                                    x-on:click="openReviewModal(row)"
                                    class="group inline-flex flex-col items-center text-center focus:outline-none cursor-pointer rounded-md p-1 transition-colors hover:bg-neutral-100/80 dark:hover:bg-neutral-800/60"
                                    :title="row.can_review ? 'Klik untuk melihat riwayat atau reviu ulang' : 'Klik untuk melihat hasil reviu'"
                                >
                                    <template x-if="row.latest_review_status === 'Disetujui'">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            <span>Disetujui</span>
                                            <template x-if="row.can_review">
                                                <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                            </template>
                                        </span>
                                    </template>
                                    <template x-if="row.latest_review_status === 'Koreksi'">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                            <span>Koreksi</span>
                                            <template x-if="row.can_review">
                                                <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                            </template>
                                        </span>
                                    </template>

                                    <template x-if="row.latest_review_date">
                                        <span class="mt-1 text-[11px] font-medium text-neutral-500 group-hover:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-200" x-text="row.latest_review_date"></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit Sprin"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Sprin"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        <x-audit.sprin-modals />
    </div>
</x-admin-layout>
