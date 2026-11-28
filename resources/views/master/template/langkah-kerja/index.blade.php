<x-admin-layout :breadcrumbs="['Data Master', 'Template', 'Langkah Kerja']">
    <div class="space-y-6" x-data="templateLangkahKerjaDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Template Langkah Kerja</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Kelola master template Langkah Kerja Audit berdasarkan lingkup Dinas maupun Kecamatan.
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Langkah Kerja</span>
            </button>
        </div>

        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari nama template, level, wilayah, pembuat..."
            emptyMessage="Belum ada template langkah kerja yang tersimpan."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Level"
                        placeholder="Semua Level"
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
                    {{-- 1. # --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 2. Opsi (2 baris: 2 tombol di atas, 2 tombol di bawah) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                        <div class="flex flex-col items-center justify-center gap-1">
                            {{-- Baris 1: Detail Langkah Kerja & Duplikasi --}}
                            <div class="inline-flex items-center justify-center gap-1">
                                <a
                                    :href="row.detail_url || `${baseUrl}/${row.id}/detail`"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-indigo-200 bg-indigo-50/70 text-indigo-700 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60"
                                    title="Detail Langkah Kerja"
                                >
                                    <x-lucide-list-checks class="h-3.5 w-3.5" />
                                </a>
                                <button
                                    type="button"
                                    x-on:click="confirmDuplicate(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Duplikasi Template"
                                >
                                    <x-lucide-copy class="h-3.5 w-3.5" />
                                </button>
                            </div>

                            {{-- Baris 2: Edit & Hapus --}}
                            <div class="inline-flex items-center justify-center gap-1">
                                <button
                                    type="button"
                                    x-on:click="openEditModal(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Edit Template"
                                >
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    x-on:click="confirmDelete(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Hapus Template"
                                >
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </td>

                    {{-- 3. Nama Template --}}
                    <td class="border-r border-neutral-200 px-4 py-3 font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                        <div class="font-medium" x-text="row.nama_template"></div>
                    </td>

                    {{-- 4. Lingkup & Dinas / OPD / Kecamatan --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                        <div class="flex flex-col items-center gap-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="row.lingkup === 'Dinas'
                                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                x-text="row.lingkup"
                            ></span>
                            <template x-if="row.plot_name">
                                <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300" x-text="row.plot_name"></span>
                            </template>
                            <template x-if="!row.plot_name">
                                <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">Umum / Semua</span>
                            </template>
                        </div>
                    </td>

                    {{-- 5. Jumlah Langkah Kerja --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                        <a
                            :href="row.detail_url || `${baseUrl}/${row.id}/detail`"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-neutral-700 shadow-2xs transition-colors hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                            title="Kelola Detail Langkah Kerja"
                        >
                            <x-lucide-list-checks class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                            <span>Langkah Kerja</span>
                            <span class="inline-flex h-4 min-w-[1.25rem] items-center justify-center rounded-full bg-neutral-100 px-1.5 text-[10px] font-bold text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200" x-text="row.jumlah_langkah_kerja ?? 0">0</span>
                        </a>
                    </td>

                    {{-- 6. Dibuat Pada --}}
                    <td class="whitespace-nowrap px-4 py-3 text-center text-xs text-neutral-600 dark:text-neutral-400" x-text="row.created_at_formatted"></td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit Template Langkah Kerja --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div
                x-show="modalOpen"
                x-transition.opacity
                x-on:click="closeModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="modalOpen"
                x-transition
                class="relative z-10 my-6 w-full max-w-lg overflow-visible rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Template Langkah Kerja' : 'Tambah Template Langkah Kerja'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Isi nama template dan level cakupan wilayah kerja.
                        </p>
                    </div>
                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                {{-- Modal Form --}}
                <form x-on:submit.prevent="saveTemplate()" class="flex flex-col">
                    <div class="space-y-4 px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        {{-- 1. Nama Template --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Nama Template <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.nama_template"
                                placeholder="Contoh: Template Langkah Kerja Operasional..."
                                class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                            />
                            <template x-if="errors.nama_template">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nama_template[0]"></p>
                            </template>
                        </div>

                        {{-- 2. Level (Nasional / Wilayah) --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Level <span class="text-red-500">*</span>
                                </label>
                                <template x-if="isLingkupReadonly()">
                                    <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">Terkunci</span>
                                </template>
                            </div>
                            <div class="grid gap-2" :class="lingkups.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
                                <template x-for="lk in lingkups" :key="lk">
                                    <button
                                        type="button"
                                        x-bind:disabled="isLingkupReadonly()"
                                        x-on:click="if (!isLingkupReadonly()) { form.lingkup = lk; onLingkupChange(); }"
                                        class="h-9 rounded-md border px-3 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-85"
                                        x-bind:class="form.lingkup === lk
                                            ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                                            : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                        x-text="lk"
                                    ></button>
                                </template>
                            </div>
                            <template x-if="errors.lingkup">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.lingkup[0]"></p>
                            </template>
                        </div>

                        {{-- 3. Detail OPD / Kecamatan --}}
                        <div>
                            <template x-if="form.lingkup === 'Dinas'">
                                <div class="space-y-1.5">
                                    <template x-if="isSpdReadonly()">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                                    Dinas / OPD
                                                </label>
                                                <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">Terkunci</span>
                                            </div>
                                            <div class="mt-1 flex h-9 items-center rounded-md border border-neutral-200 bg-neutral-100 px-3 text-xs font-medium text-neutral-800 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-200" x-text="scopeName || 'Dinas Terkunci'"></div>
                                        </div>
                                    </template>
                                    <template x-if="!isSpdReadonly()">
                                        <x-searchable-select
                                            label="Dinas / OPD"
                                            placeholder="Semua Dinas / OPD (Umum)"
                                            itemsExpr="dinasOptions"
                                            valueModel="form.struktur_perangkat_daerah_id"
                                            changeAction=""
                                        />
                                    </template>
                                    <template x-if="errors.struktur_perangkat_daerah_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.struktur_perangkat_daerah_id[0]"></p>
                                    </template>
                                </div>
                            </template>

                            <template x-if="form.lingkup === 'Kecamatan'">
                                <div class="space-y-1.5">
                                    <template x-if="isSpdReadonly()">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                                    Kecamatan
                                                </label>
                                                <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">Terkunci</span>
                                            </div>
                                            <div class="mt-1 flex h-9 items-center rounded-md border border-neutral-200 bg-neutral-100 px-3 text-xs font-medium text-neutral-800 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-200" x-text="scopeName || 'Kecamatan Terkunci'"></div>
                                        </div>
                                    </template>
                                    <template x-if="!isSpdReadonly()">
                                        <x-searchable-select
                                            label="Kecamatan"
                                            placeholder="Semua Kecamatan (Umum)"
                                            itemsExpr="kecamatanOptions"
                                            valueModel="form.struktur_perangkat_daerah_id"
                                            changeAction=""
                                        />
                                    </template>
                                    <template x-if="errors.struktur_perangkat_daerah_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.struktur_perangkat_daerah_id[0]"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                        <button
                            type="button"
                            x-on:click="closeModal()"
                            class="inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            x-bind:disabled="saving"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-4 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        >
                            <template x-if="saving">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                            </template>
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan Template')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
