<x-admin-layout :breadcrumbs="['Data Master', 'Template', 'PKA']">
    <div class="space-y-6" x-data="templatePkaDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Template PKA (Program Kerja Audit)</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Kelola master format template Program Kerja Audit dengan editor dokumen kaya fitur.
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Template PKA</span>
            </button>
        </div>


        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari nama template, lingkup, wilayah..."
            emptyMessage="Belum ada template PKA yang tersimpan."
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
                    <td class="border-r border-neutral-200 px-4 py-3 font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                        <div class="font-medium" x-text="row.nama_template"></div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 dark:border-neutral-800 text-center">
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
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center text-xs text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.created_at_formatted"></td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openPreviewModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Lihat Isi Template"
                            >
                                <x-lucide-eye class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit Template"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDuplicate(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Duplikasi Template"
                            >
                                <x-lucide-copy class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Template"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit Template PKA --}}
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
                class="relative z-10 my-6 w-full max-w-5xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Template PKA' : 'Tambah Template PKA'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Konfigurasikan lingkup dan lengkapi setiap bagian format isian Program Kerja Audit.
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
                <form x-on:submit.prevent="saveTemplate()" class="flex flex-col max-h-[85vh]">
                    <div class="space-y-5 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        {{-- Metadata: Nama Template, Lingkup, Plotting / Wilayah --}}
                        <div class="grid gap-4 sm:grid-cols-3">
                            {{-- 1. Nama Template --}}
                            <div class="sm:col-span-1 space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    1. Nama Template <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    x-model="form.nama_template"
                                    placeholder="Contoh: Template PKA Operasional..."
                                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                />
                                <template x-if="errors.nama_template">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nama_template[0]"></p>
                                </template>
                            </div>

                            {{-- 2. Lingkup --}}
                            <div class="sm:col-span-1 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        2. Lingkup <span class="text-red-500">*</span>
                                    </label>
                                    <template x-if="isLingkupReadonly()">
                                        <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">Terkunci</span>
                                    </template>
                                </div>
                                <div class="grid gap-1.5" :class="lingkups.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
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

                            {{-- Detail OPD / Kecamatan --}}
                            <div class="sm:col-span-1 space-y-1.5">
                                <template x-if="form.lingkup === 'Dinas'">
                                    <div>
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
                                    <div>
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

                        {{-- Section Tabs (Items 3 - 9) --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                                    Bagian Isian Dokumen PKA (Format TipTap Rich Text)
                                </label>
                                <span class="text-[11px] text-neutral-400 dark:text-neutral-500">
                                    Pilih tab bagian untuk menyunting kontennya
                                </span>
                            </div>

                            {{-- Tab Navigation --}}
                            <div class="flex flex-wrap gap-1.5 border-b border-neutral-200 pb-2 dark:border-neutral-800">
                                <template x-for="sec in sections" :key="sec.key">
                                    <button
                                        type="button"
                                        x-on:click="switchTab(sec.key)"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-all"
                                        :class="activeTab === sec.key
                                            ? 'bg-neutral-900 text-white shadow-xs dark:bg-neutral-100 dark:text-neutral-900'
                                            : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200/80 hover:text-neutral-900 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200'"
                                    >
                                        <span x-text="sec.label"></span>
                                        <template x-if="form[sec.key] && form[sec.key].replace(/<[^>]*>?/gm, '').trim() !== ''">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="activeTab === sec.key ? 'bg-emerald-400 dark:bg-emerald-600' : 'bg-emerald-500'"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>

                            {{-- TipTap Editor Area with MS-Word Style Toolbar --}}
                            <div class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                <x-tiptap-toolbar />

                                {{-- Editor Canvas per Tab --}}
                                @foreach ([
                                    'pendahuluan' => '1. Pendahuluan',
                                    'tujuan' => '2. Tujuan',
                                    'ruang_lingkup' => '3. Ruang Lingkup Audit',
                                    'sasaran' => '4. Sasaran Audit',
                                    'waktu_pelaksanaan' => '5. Waktu Pelaksanaan',
                                    'periode' => '6. Periode Audit',
                                    'penutup' => '7. Penutup',
                                ] as $key => $label)
                                    <div x-show="activeTab === '{{ $key }}'" class="flex flex-col">
                                        <div
                                            x-ref="editor_{{ $key }}"
                                            x-on:click="focusActiveEditor($event)"
                                            class="tiptap min-h-[280px] max-h-[460px] overflow-y-auto cursor-text bg-white dark:bg-neutral-950"
                                        ></div>
                                        {{-- Per-tab Action Bar --}}
                                        <div class="flex items-center justify-between border-t border-neutral-200 bg-neutral-50/70 px-4 py-2.5 dark:border-neutral-800 dark:bg-neutral-900/50">
                                            <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                                                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                                <span>Sedang menyunting bagian: <strong class="text-neutral-700 dark:text-neutral-200">{{ $label }}</strong></span>
                                            </div>
                                            <button
                                                type="button"
                                                x-on:click="saveCurrentTab()"
                                                x-bind:disabled="savingSection"
                                                class="inline-flex h-8 items-center gap-1.5 rounded-md border border-neutral-300 bg-white px-3 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-50 disabled:opacity-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                            >
                                                <template x-if="savingSection">
                                                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-neutral-400 border-t-neutral-800 dark:border-neutral-500 dark:border-t-white"></span>
                                                </template>
                                                <template x-if="!savingSection">
                                                    <x-lucide-save class="h-3.5 w-3.5" />
                                                </template>
                                                <span>Simpan Bagian Ini</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
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

        {{-- Modal Preview Template PKA --}}
        <div
            x-show="previewModalOpen"
            x-cloak
            x-on:keydown.escape.window="closePreviewModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div
                x-show="previewModalOpen"
                x-transition.opacity
                x-on:click="closePreviewModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="previewModalOpen"
                x-transition
                class="relative z-10 my-6 w-full max-w-4xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                            <x-lucide-eye class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="previewData ? previewData.nama_template : 'Detail Template PKA'"></h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Pratinjau susunan format dan isi Program Kerja Audit.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        x-on:click="closePreviewModal()"
                        class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto p-6 space-y-6">
                    <template x-if="loadingPreview">
                        <div class="flex flex-col items-center justify-center py-12 text-sm text-neutral-500">
                            <span class="h-6 w-6 animate-spin rounded-full border-2 border-neutral-300 border-t-neutral-800"></span>
                            <span class="mt-2">Memuat isi template...</span>
                        </div>
                    </template>

                    <template x-if="!loadingPreview && previewData">
                        <div class="space-y-6">
                            {{-- Info Header Card --}}
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-neutral-50/70 p-4 dark:border-neutral-800 dark:bg-neutral-900/40">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Lingkup:</span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="previewData.lingkup === 'Dinas'
                                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                        x-text="previewData.lingkup"
                                    ></span>
                                    <template x-if="previewData.plot_name">
                                        <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300" x-text="previewData.plot_name"></span>
                                    </template>
                                    <template x-if="!previewData.plot_name">
                                        <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">Umum / Semua</span>
                                    </template>
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Dibuat pada: <span class="font-medium text-neutral-700 dark:text-neutral-300" x-text="previewData.created_at"></span>
                                </div>
                            </div>

                            {{-- Formatted Sections --}}
                            <div class="space-y-5">
                                <template x-for="(sec, sIdx) in sections" :key="sec.key">
                                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900/30">
                                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 border-b border-neutral-100 pb-2 dark:border-neutral-800" x-text="sec.label"></h3>
                                        <div
                                            class="tiptap prose prose-sm max-w-none dark:prose-invert"
                                            x-html="previewData[sec.key] || '<p class=\'text-xs text-neutral-400 italic\'>Belum ada isi.</p>'"
                                        ></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end border-t border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                    <button
                        type="button"
                        x-on:click="closePreviewModal()"
                        class="inline-flex h-9 items-center justify-center rounded-md bg-neutral-900 px-4 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Konfirmasi Duplikasi --}}
        <div
            x-show="duplicateModalOpen"
            x-cloak
            x-on:keydown.escape.window="closeDuplicateModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div
                x-show="duplicateModalOpen"
                x-transition.opacity
                x-on:click="closeDuplicateModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="duplicateModalOpen"
                x-transition
                class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                        <x-lucide-copy class="h-5 w-5" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Duplikasi Template PKA</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Apakah Anda yakin ingin menduplikasi template <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="duplicatingItem ? duplicatingItem.nama_template : ''"></span>? Salinan baru akan dibuat dengan seluruh isian dokumen yang sama persis.
                        </p>
                    </div>
                </div>

                <template x-if="duplicateError">
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-2 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="duplicateError"></div>
                </template>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        x-on:click="closeDuplicateModal()"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        x-bind:disabled="duplicating"
                        x-on:click="duplicateItem()"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-4 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                    >
                        <template x-if="duplicating">
                            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                        </template>
                        <span x-text="duplicating ? 'Menduplikasi...' : 'Ya, Duplikasi'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Konfirmasi Hapus --}}
        <div
            x-show="deleteModalOpen"
            x-cloak
            x-on:keydown.escape.window="closeDeleteModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div
                x-show="deleteModalOpen"
                x-transition.opacity
                x-on:click="closeDeleteModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="deleteModalOpen"
                x-transition
                class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                        <x-lucide-trash-2 class="h-5 w-5" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Template PKA</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Apakah Anda yakin ingin menghapus template <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="deletingItem ? deletingItem.nama_template : ''"></span>? Template ini dapat dipulihkan melalui soft delete.
                        </p>
                    </div>
                </div>

                <template x-if="deleteError">
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-2 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="deleteError"></div>
                </template>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        x-on:click="closeDeleteModal()"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        x-bind:disabled="deleting"
                        x-on:click="deleteItem()"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-red-600 px-4 text-xs font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50"
                    >
                        <template x-if="deleting">
                            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                        </template>
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
