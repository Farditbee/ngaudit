<x-admin-layout :breadcrumbs="['Data Master', 'Kode', 'Kriteria']">
    <div class="space-y-6" x-data="kriteriaDataTable(@js($tableConfig))">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data Kriteria</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Kelola daftar peraturan atau standar kriteria pemeriksaan beserta pemetaan bidang (Opsnal, Logistik, SDM, Garkeu).
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Kriteria</span>
            </button>
        </div>

        <x-data-table
            :fixed="true"
            searchPlaceholder="Cari nama kriteria..."
            emptyMessage="Belum ada data kriteria. Klik tombol Tambah Kriteria untuk menambahkan data baru."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Bidang"
                        placeholder="Semua Bidang"
                        itemsExpr="bidangOptions"
                        valueModel="filterBidang"
                        changeAction="onFilterChange()"
                    />
                </div>

                <div class="mt-3 flex justify-end" x-show="filterBidang !== ''" x-cloak>
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
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                    <td class="whitespace-normal break-words border-r border-neutral-200 px-4 py-3 font-medium text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.nama"></td>

                    <template x-for="field in ['is_opsnal', 'is_logistik', 'is_sdm', 'is_garkeu']" :key="field">
                        <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                            <span
                                x-show="Boolean(row[field])"
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                            >
                                <x-lucide-check class="h-3.5 w-3.5" />
                            </span>
                            <span
                                x-show="!Boolean(row[field])"
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-neutral-100 text-neutral-400 dark:bg-neutral-900 dark:text-neutral-600"
                            >
                                <x-lucide-minus class="h-3.5 w-3.5" />
                            </span>
                        </td>
                    </template>

                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Create / Edit --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div x-show="modalOpen" x-transition.opacity x-on:click="closeModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

            <div x-show="modalOpen" x-transition class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Kriteria' : 'Tambah Kriteria'"></h2>
                    <button type="button" x-on:click="closeModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <form x-on:submit.prevent="saveKriteria()" class="space-y-5 px-6 py-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            Nama Kriteria <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            rows="3"
                            x-model="form.nama"
                            placeholder="Contoh: Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah"
                            class="block w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                        ></textarea>
                        <template x-if="errors.nama">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nama[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            Bidang Pemeriksaan
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-neutral-200 px-3 py-2.5 text-sm hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900">
                                <input type="checkbox" x-model="form.is_opsnal" class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">Operasional (Opsnal)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-neutral-200 px-3 py-2.5 text-sm hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900">
                                <input type="checkbox" x-model="form.is_logistik" class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">Logistik</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-neutral-200 px-3 py-2.5 text-sm hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900">
                                <input type="checkbox" x-model="form.is_sdm" class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">SDM</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-neutral-200 px-3 py-2.5 text-sm hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900">
                                <input type="checkbox" x-model="form.is_garkeu" class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">Anggaran & Keuangan (Garkeu)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                        <button type="button" x-on:click="closeModal()" class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800">
                            Batal
                        </button>
                        <button type="submit" x-bind:disabled="saving" class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                            <x-lucide-loader-circle x-show="saving" x-cloak class="h-4 w-4 animate-spin" />
                            <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
