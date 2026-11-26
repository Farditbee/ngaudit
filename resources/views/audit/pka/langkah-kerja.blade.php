@php
    $sprin = $pka->sprin;
    $daltuName = $sprin?->pengendaliMutu?->name ?? '-';
    $dalnisName = $sprin?->pengawasTim?->name ?? '-';
    $katimNames = collect([$sprin?->ketuaTim?->name, $sprin?->ketuaTim2?->name])->filter()->implode(', ') ?: '-';
    $auditorNames = collect($auditors)->pluck('label')->filter()->implode(', ') ?: '-';
@endphp

<x-admin-layout :breadcrumbs="['Audit', 'Persiapan', 'PKA', 'Langkah Kerja PKA']">
    <x-slot:title>Langkah Kerja PKA</x-slot:title>

    <div
        x-data="pkaLangkahKerjaDataTable({{ Js::from($tableConfig) }})"
        class="space-y-6"
    >
        {{-- Top Page Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">PKA</p>
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    Langkah Kerja PKA
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('audit.pka.index') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-blue-600 px-4 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700"
                >
                    <x-lucide-arrow-left class="h-3.5 w-3.5" />
                    <span>Kembali</span>
                </a>

                <button
                    type="button"
                    x-on:click="printPka()"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-emerald-700"
                >
                    <x-lucide-printer class="h-3.5 w-3.5" />
                    <span>Cetak PKA</span>
                </button>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-950 space-y-6">
            {{-- Metadata PKA & Sprin --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50/40 text-sm dark:border-neutral-800 dark:bg-neutral-900/30">
                {{-- Baris 1: Nomor Audit Rutin, Nomor PKA, Bidang Audit --}}
                <div class="grid divide-y divide-neutral-200 border-b border-neutral-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:divide-neutral-800 dark:border-neutral-800">
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Nomor Audit Rutin</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $sprin?->no_sprin ?? '-' }}</p>
                    </div>
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Nomor PKA</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $pka->nomor_pka }}</p>
                    </div>
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Bidang Audit</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $pka->bidang }}</p>
                    </div>
                </div>

                {{-- Baris 2: Koordinator dan Pengendali, Pengawas Tim, Ketua Tim --}}
                <div class="grid divide-y divide-neutral-200 border-b border-neutral-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:divide-neutral-800 dark:border-neutral-800">
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Koordinator dan Pengendali</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $daltuName }}</p>
                    </div>
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Pengawas Tim</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $dalnisName }}</p>
                    </div>
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Ketua Tim</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ $katimNames }}</p>
                    </div>
                </div>

                {{-- Baris 3: Anggota (Auditor) --}}
                <div class="border-b border-neutral-200 p-4 dark:border-neutral-800">
                    <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Anggota (Auditor)</span>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @forelse ($auditors as $idx => $auditor)
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-semibold text-neutral-800 shadow-2xs dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-neutral-100 text-[10px] font-bold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">{{ $idx + 1 }}</span>
                                <span>{{ $auditor['label'] }}</span>
                            </span>
                        @empty
                            <span class="text-xs italic text-neutral-400">-</span>
                        @endforelse
                    </div>
                </div>

                {{-- Baris 4: Tanggal & Anggaran --}}
                <div class="grid divide-y divide-neutral-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:divide-neutral-800">
                    <div class="p-4">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Tanggal</span>
                        <p class="mt-1 font-semibold text-neutral-800 dark:text-neutral-200">{{ $pka->tanggal?->format('Y-m-d') ?? '-' }}</p>
                    </div>
                    <div class="p-4 sm:col-span-2">
                        <span class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Anggaran</span>
                        <p class="mt-1 font-bold text-neutral-900 dark:text-neutral-100">{{ number_format((float) $pka->anggaran, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Langkah Kerja Action Section & Gunakan Template --}}
            <div class="border-t border-neutral-200 pt-5 dark:border-neutral-800 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-neutral-100">Langkah Kerja</h2>

                        <button
                            type="button"
                            x-show="selectedIds.length > 0"
                            x-cloak
                            x-on:click="confirmBulkDelete()"
                            x-bind:disabled="bulkDeleting"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md bg-red-600 px-3 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-red-700 disabled:opacity-50"
                        >
                            <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                        </button>

                        <button
                            type="button"
                            x-show="total > 0"
                            x-cloak
                            x-on:click="confirmClearAll()"
                            x-bind:disabled="clearing"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 disabled:opacity-50 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300"
                        >
                            <x-lucide-eraser class="h-3.5 w-3.5" />
                            <span>Kosongkan Semua</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        x-on:click="openCreateModal()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700"
                    >
                        <span>Tambah</span>
                    </button>
                </div>

                {{-- Gunakan Template Selector (Rata Kanan) --}}
                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-2">
                        <p class="text-xs italic text-neutral-500 dark:text-neutral-400">atau</p>
                        <div>
                            <x-searchable-select
                                label="Gunakan Template"
                                placeholder="-Pilih Template Langkah Kerja-"
                                itemsExpr="templateOptions"
                                valueModel="selectedTemplateId"
                                changeAction=""
                                :wrap="true"
                            />
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                x-on:click="applyTemplate()"
                                x-bind:disabled="applyingTemplate || !selectedTemplateId"
                                class="inline-flex h-8 items-center gap-1.5 rounded-md bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                            >
                                <x-lucide-loader-circle x-show="applyingTemplate" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                <span>Gunakan Template</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Toolbar Search & PerPage --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                    <span>Tampilkan</span>
                    <select
                        x-model.number="perPage"
                        x-on:change="onPerPageChange()"
                        class="h-8 rounded-md border border-neutral-200 bg-white py-1 pl-2.5 pr-7 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>data</span>
                </div>

                <div class="relative w-full sm:w-64">
                    <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
                    <input
                        type="search"
                        x-model="search"
                        x-on:input.debounce.350ms="onSearchInput()"
                        placeholder="Cari uraian, pelaksana, catatan..."
                        class="block h-8 w-full rounded-md border border-neutral-200 bg-white pl-8 pr-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                </div>
            </div>

            {{-- Tabel Langkah Kerja (2-row grouped header sesuai gambar) --}}
            <div class="relative overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-800">
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

                <table class="w-full table-fixed border-collapse text-left text-xs">
                    <thead>
                        {{-- Header Row 1 --}}
                        <tr class="border-b border-neutral-200 bg-neutral-50/80 text-[11px] font-semibold uppercase tracking-wider text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900/60 dark:text-neutral-400">
                            <th rowspan="2" class="w-10 border-r border-neutral-200 px-2.5 py-2.5 text-center align-bottom dark:border-neutral-800">
                                <div class="flex flex-col items-center gap-1">
                                    <input
                                        type="checkbox"
                                        x-bind:checked="isAllPageSelected()"
                                        x-on:change="toggleSelectAllPage()"
                                        title="Pilih Semua"
                                        class="h-3.5 w-3.5 cursor-pointer rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900"
                                    />
                                    <span>#</span>
                                </div>
                            </th>
                            <th rowspan="2" class="w-12 border-r border-neutral-200 px-2.5 py-2.5 text-center align-bottom dark:border-neutral-800">
                                NO
                            </th>
                            <th rowspan="2" class="border-r border-neutral-200 px-4 py-2.5 align-bottom dark:border-neutral-800">
                                URAIAN
                            </th>
                            <th colspan="2" class="w-64 border-b border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                DILAKSANAKAN OLEH
                            </th>
                            <th colspan="2" class="w-48 border-b border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                WAKTU YANG DIPERLUKAN
                            </th>
                            <th rowspan="2" class="w-20 px-3 py-2.5 text-center align-bottom">
                                AKSI
                            </th>
                        </tr>
                        {{-- Header Row 2 --}}
                        <tr class="border-b border-neutral-200 bg-neutral-50/80 text-[11px] font-semibold uppercase tracking-wider text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900/60 dark:text-neutral-400">
                            <th class="w-32 border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                RENCANA
                            </th>
                            <th class="w-32 border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                REALISASI
                            </th>
                            <th class="w-24 border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                RENCANA
                            </th>
                            <th class="w-24 border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                                REALISASI
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-if="!loading && rows.length === 0">
                            <tr>
                                <td colspan="8" class="px-6 py-6 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                    - Belum ada data -
                                </td>
                            </tr>
                        </template>

                        <template x-for="(row, rIndex) in rows" :key="row.id">
                            <tr
                                class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                                :class="isSelected(row.id)
                                    ? 'bg-blue-50/60 dark:bg-blue-950/30'
                                    : (rIndex % 2 === 0 ? 'bg-white dark:bg-neutral-950' : 'bg-neutral-50/40 dark:bg-neutral-900/20')"
                            >
                                {{-- 1. Checkbox (#) --}}
                                <td class="w-10 whitespace-nowrap border-r border-neutral-200 px-2.5 py-3 text-center align-top dark:border-neutral-800">
                                    <input
                                        type="checkbox"
                                        x-bind:checked="isSelected(row.id)"
                                        x-on:change="toggleSelectRow(row.id)"
                                        class="mt-0.5 h-3.5 w-3.5 cursor-pointer rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900"
                                    />
                                </td>

                                {{-- 2. NO --}}
                                <td class="w-12 whitespace-nowrap border-r border-neutral-200 px-2.5 py-3 text-center align-top font-semibold text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                                {{-- 3. URAIAN --}}
                                <td class="border-r border-neutral-200 px-4 py-3 align-top break-words text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                                    <div class="tiptap prose prose-sm max-w-none dark:prose-invert" x-html="row.uraian"></div>
                                </td>

                                {{-- 4. DILAKSANAKAN OLEH - RENCANA --}}
                                <td class="w-32 border-r border-neutral-200 px-3 py-3 text-center align-top break-words text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.rencana_name || '-'"></td>

                                {{-- 5. DILAKSANAKAN OLEH - REALISASI --}}
                                <td class="w-32 border-r border-neutral-200 px-3 py-3 text-center align-top break-words text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.realisasi_name || '-'"></td>

                                {{-- 6. WAKTU YANG DIPERLUKAN - RENCANA --}}
                                <td class="w-24 whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center align-top text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.waktu_rencana_formatted || '-'"></td>

                                {{-- 7. WAKTU YANG DIPERLUKAN - REALISASI --}}
                                <td class="w-24 whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center align-top text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.waktu_realisasi_formatted || '-'"></td>

                                {{-- 8. AKSI --}}
                                <td class="w-20 whitespace-nowrap px-3 py-3 text-center align-top">
                                    <div class="inline-flex items-center justify-center gap-1">
                                        <button
                                            type="button"
                                            x-on:click="openEditModal(row)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                            title="Edit Langkah Kerja"
                                        >
                                            <x-lucide-pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="confirmDelete(row)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                            title="Hapus Langkah Kerja"
                                        >
                                            <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="flex flex-wrap items-center justify-between gap-4 text-xs">
                <p class="text-neutral-500 dark:text-neutral-400">
                    Menampilkan
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="startEntry().toLocaleString('id-ID')"></span>
                    sampai
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="endEntry().toLocaleString('id-ID')"></span>
                    dari
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="filtered.toLocaleString('id-ID')"></span>
                    data
                </p>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        x-on:click="goToPage(page - 1)"
                        x-bind:disabled="page <= 1 || loading"
                        class="flex h-8 items-center gap-1 rounded-md px-2.5 text-xs hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <x-lucide-chevron-left class="h-3.5 w-3.5" />
                        <span>Sebelumnya</span>
                    </button>

                    <template x-for="(p, i) in visiblePages()" :key="p + '-' + i">
                        <button
                            type="button"
                            x-on:click="goToPage(p)"
                            x-bind:disabled="p === '...' || loading"
                            x-text="p"
                            class="h-8 min-w-8 rounded-md px-2 text-xs"
                            x-bind:class="page === p
                                ? 'border border-neutral-200 bg-neutral-100 font-medium text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'
                                : (p === '...' ? 'cursor-default text-neutral-400' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800')"
                        ></button>
                    </template>

                    <button
                        type="button"
                        x-on:click="goToPage(page + 1)"
                        x-bind:disabled="page >= totalPages() || loading"
                        class="flex h-8 items-center gap-1 rounded-md px-2.5 text-xs hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <span>Selanjutnya</span>
                        <x-lucide-chevron-right class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Tambah / Edit Langkah Kerja PKA --}}
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
                class="relative z-10 my-6 w-full max-w-4xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Langkah Kerja PKA' : 'Tambah Langkah Kerja PKA'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Lengkapi uraian langkah kerja, pelaksana (rencana & realisasi), waktu yang diperlukan, serta catatan.
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
                <form x-on:submit.prevent="saveItem()" class="flex flex-col max-h-[85vh]">
                    <div class="space-y-5 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        {{-- 1. Bidang (Otomatis Lock ke PKA) --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Bidang <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">Otomatis Terkunci ke PKA</span>
                            </div>
                            <input
                                type="text"
                                :value="bidang"
                                readonly
                                disabled
                                class="block h-9 w-full cursor-not-allowed rounded-md border border-neutral-200 bg-neutral-100 px-3 text-xs font-semibold text-neutral-700 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-300"
                            />
                        </div>

                        {{-- 2. Uraian (TipTap Rich Text Area) --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Uraian Langkah Kerja <span class="text-red-500">*</span>
                            </label>

                            <div class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                {{-- Toolbar --}}
                                <div class="flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-neutral-50/80 p-2 dark:border-neutral-800 dark:bg-neutral-900/60">
                                    {{-- Undo / Redo --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="undo()"
                                            :disabled="!canUndo()"
                                            title="Undo (Ctrl+Z)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 disabled:opacity-40 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-undo class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="redo()"
                                            :disabled="!canRedo()"
                                            title="Redo (Ctrl+Y)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 disabled:opacity-40 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-redo class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Headings / Paragraph --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setHeading(0)"
                                            :class="isActive('paragraph') ? 'bg-neutral-200 font-semibold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Paragraf Normal"
                                            class="inline-flex h-8 px-2 items-center justify-center rounded-md text-xs"
                                        >
                                            P
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(1)"
                                            :class="isActive('heading', { level: 1 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 1"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H1
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(2)"
                                            :class="isActive('heading', { level: 2 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 2"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H2
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(3)"
                                            :class="isActive('heading', { level: 3 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 3"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H3
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Formatting: Bold, Italic, Underline, Strike --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleBold()"
                                            :class="isActive('bold') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Tebal (Ctrl+B)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-bold class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleItalic()"
                                            :class="isActive('italic') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Miring (Ctrl+I)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-italic class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleUnderline()"
                                            :class="isActive('underline') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Garis Bawah (Ctrl+U)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-underline class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleStrike()"
                                            :class="isActive('strike') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Coretan (Strikethrough)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-strikethrough class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Pangkat: Superscript, Subscript --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleSuperscript()"
                                            :class="isActive('superscript') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Pangkat Atas (Superscript)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-superscript class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleSubscript()"
                                            :class="isActive('subscript') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Pangkat Bawah (Subscript)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-subscript class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Alignment: Left, Center, Right, Justify --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('left')"
                                            :class="isActive({ textAlign: 'left' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kiri"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-left class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('center')"
                                            :class="isActive({ textAlign: 'center' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Tengah"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-center class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('right')"
                                            :class="isActive({ textAlign: 'right' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kanan"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-right class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('justify')"
                                            :class="isActive({ textAlign: 'justify' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kiri-Kanan (Justify)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-justify class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Lists & Multilevel Indent --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleBulletList()"
                                            :class="isActive('bulletList') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Bullet List"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-list class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleOrderedList()"
                                            :class="isActive('orderedList') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Numbered List"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-list-ordered class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="liftListItem()"
                                            title="Kurangi Indent (Shift+Tab)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-outdent class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="sinkListItem()"
                                            title="Tambah Indent (Tab / Multilevel)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-indent class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Table Controls --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="insertTable(3, 3)"
                                            title="Sisipkan Tabel (3x3)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-table class="h-4 w-4" />
                                        </button>
                                        <template x-if="isActive('table')">
                                            <div class="inline-flex items-center gap-0.5">
                                                <button
                                                    type="button"
                                                    x-on:click="addRowAfter()"
                                                    title="Tambah Baris Bawah"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                                >
                                                    +Baris
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="addColumnAfter()"
                                                    title="Tambah Kolom Kanan"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                                >
                                                    +Kolom
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteRow()"
                                                    title="Hapus Baris"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    -Baris
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteColumn()"
                                                    title="Hapus Kolom"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    -Kolom
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteTable()"
                                                    title="Hapus Tabel"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    Hapus Tabel
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Embed, Link, Image & Clear Formatting --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setLink()"
                                            :class="isActive('link') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Sisipkan Link"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-link class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="insertImage()"
                                            title="Sisipkan Gambar / Embed"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-image class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="clearFormatting()"
                                            title="Bersihkan Format"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-remove-formatting class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                {{-- Editor Canvas --}}
                                <div
                                    x-ref="editor_uraian"
                                    x-on:click="focusEditor($event)"
                                    class="tiptap min-h-[240px] max-h-[380px] overflow-y-auto cursor-text bg-white dark:bg-neutral-950"
                                ></div>
                            </div>

                            <template x-if="errors.uraian">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian[0]"></p>
                            </template>
                        </div>

                        {{-- 3. Dilaksanakan oleh: Rencana & Realisasi (Searchable Select) --}}
                        <div class="space-y-1.5">
                            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Dilaksanakan oleh</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1">
                                    <x-searchable-select
                                        label="Rencana"
                                        placeholder="Silahkan Pilih"
                                        itemsExpr="auditorOptions"
                                        valueModel="form.dilaksanakan_rencana_id"
                                        changeAction=""
                                    />
                                    <template x-if="errors.dilaksanakan_rencana_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.dilaksanakan_rencana_id[0]"></p>
                                    </template>
                                </div>

                                <div class="space-y-1">
                                    <x-searchable-select
                                        label="Realisasi"
                                        placeholder="Silahkan Pilih"
                                        itemsExpr="auditorOptions"
                                        valueModel="form.dilaksanakan_realisasi_id"
                                        changeAction=""
                                    />
                                    <template x-if="errors.dilaksanakan_realisasi_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.dilaksanakan_realisasi_id[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Waktu yang diperlukan (Rencana & Realisasi dalam Hari) & Catatan (Sesuai Gambar 3) --}}
                        <div class="space-y-1.5">
                            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Waktu yang diperlukan</p>
                            <div class="grid gap-4 sm:grid-cols-12">
                                {{-- Waktu Rencana --}}
                                <div class="sm:col-span-3 space-y-1">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        Rencana
                                    </label>
                                    <div class="flex rounded-md shadow-xs">
                                        <input
                                            type="number"
                                            min="0"
                                            step="1"
                                            x-model="form.waktu_rencana"
                                            placeholder="Masukkan angka"
                                            class="block h-9 w-full rounded-l-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                        />
                                        <span class="inline-flex items-center rounded-r-md border border-l-0 border-neutral-200 bg-neutral-50 px-3 text-xs font-medium text-neutral-600 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                                            Hari
                                        </span>
                                    </div>
                                    <template x-if="errors.waktu_rencana">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.waktu_rencana[0]"></p>
                                    </template>
                                </div>

                                {{-- Waktu Realisasi --}}
                                <div class="sm:col-span-3 space-y-1">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        Realisasi
                                    </label>
                                    <div class="flex rounded-md shadow-xs">
                                        <input
                                            type="number"
                                            min="0"
                                            step="1"
                                            x-model="form.waktu_realisasi"
                                            placeholder="Masukkan angka"
                                            class="block h-9 w-full rounded-l-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                        />
                                        <span class="inline-flex items-center rounded-r-md border border-l-0 border-neutral-200 bg-neutral-50 px-3 text-xs font-medium text-neutral-600 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                                            Hari
                                        </span>
                                    </div>
                                    <template x-if="errors.waktu_realisasi">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.waktu_realisasi[0]"></p>
                                    </template>
                                </div>

                                {{-- Catatan --}}
                                <div class="sm:col-span-6 space-y-1">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        Catatan
                                    </label>
                                    <input
                                        type="text"
                                        x-model="form.catatan"
                                        placeholder="Masukkan catatan..."
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                    <template x-if="errors.catatan">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.catatan[0]"></p>
                                    </template>
                                </div>
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
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-blue-600 px-5 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 disabled:opacity-50"
                        >
                            <template x-if="saving">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                            </template>
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan Langkah Kerja')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
