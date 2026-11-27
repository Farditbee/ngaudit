<x-admin-layout :breadcrumbs="['Audit', 'Pelaksanaan', 'KKA']">
    <x-slot:title>Kertas Kerja Audit (KKA)</x-slot:title>

    @php
        $inputClass = 'block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100';
        $readonlyClass = 'flex min-h-9 items-center rounded-md border border-neutral-200 bg-neutral-100 px-3 py-1.5 text-xs font-medium text-neutral-800 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-200';
        $filterSelectClass = 'block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-800 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200';
        $rowButtonClass = 'inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800';
    @endphp

    <div x-data="kkaDataTable({{ Js::from($dataTableConfig) }})" class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    Kertas Kerja Audit (KKA)
                </h1>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Pencatatan Kertas Kerja Audit berdasarkan Program Kerja Audit (PKA) beserta tujuan, langkah, dan hasil pengujian.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    x-on:click="openCreateModal()"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-3.5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    <x-lucide-plus class="h-4 w-4" />
                    <span>Tambah KKA</span>
                </button>
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Bidang</label>
                <select x-model="filterBidang" x-on:change="onFilterChange()" class="{{ $filterSelectClass }}">
                    <option value="">Semua Bidang</option>
                    <template x-for="b in bidangs" :key="b">
                        <option :value="b" x-text="b"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Jenis Audit</label>
                <select x-model="filterJenisAudit" x-on:change="onFilterChange()" class="{{ $filterSelectClass }}">
                    <option value="">Semua Jenis Audit</option>
                    <template x-for="ja in jenisAudits" :key="ja">
                        <option :value="ja" x-text="ja"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Status</label>
                <select x-model="filterStatus" x-on:change="onFilterChange()" class="{{ $filterSelectClass }}">
                    <option value="">Semua Status</option>
                    <template x-for="st in statuses" :key="st">
                        <option :value="st" x-text="st"></option>
                    </template>
                </select>
            </div>
            <div class="flex items-end">
                <button
                    type="button"
                    x-on:click="resetFilters()"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-neutral-200 bg-white px-3 text-xs font-medium text-neutral-600 shadow-xs hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                >
                    <x-lucide-rotate-ccw class="h-3.5 w-3.5" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        {{-- DataTable --}}
        <x-data-table
            :columns="$columns"
            search-placeholder="Cari nomor sprin, PKA, auditor, auditee, tempat..."
            empty-message="Belum ada Kertas Kerja Audit yang tersimpan."
            :page-length="10"
        >
            <template x-for="(row, index) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-50/70 dark:border-neutral-800 dark:hover:bg-neutral-900/50"
                    :class="index % 2 === 1 ? 'bg-neutral-50/40 dark:bg-neutral-900/20' : ''"
                >
                    {{-- 1. # --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 2. Nomor Sprin, PKA & KKA (bertumpuk) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-xs dark:border-neutral-800">
                        <div class="space-y-1">
                            <div>
                                <span class="font-medium text-red-600 underline decoration-red-400 underline-offset-2 dark:text-red-400 dark:decoration-red-500" x-text="row.no_sprin"></span>
                            </div>
                            <div>
                                <span class="font-medium text-amber-600 underline decoration-amber-400 underline-offset-2 dark:text-amber-400 dark:decoration-amber-500" x-text="row.nomor_pka"></span>
                            </div>
                            <div>
                                <span class="font-medium text-emerald-600 underline decoration-emerald-400 underline-offset-2 dark:text-emerald-400 dark:decoration-emerald-500" x-text="row.nomor_kka || ('KKA ' + row.tanggal_kka_formatted)"></span>
                            </div>
                        </div>
                    </td>

                    {{-- 3. Auditor (Dibuat Oleh) --}}
                    <td class="border-r border-neutral-200 px-4 py-3 text-xs font-medium text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                        <span x-text="row.auditor_name || '-'"></span>
                    </td>

                    {{-- 4. Auditee --}}
                    <td class="border-r border-neutral-200 px-4 py-3 text-xs dark:border-neutral-800">
                        <div class="flex flex-col items-start gap-1">
                            <template x-for="(item, hIdx) in (row.auditee_hierarchy || [])" :key="hIdx">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] leading-tight"
                                    :class="{
                                        'bg-amber-100 font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': item.is_target,
                                        'bg-purple-100 font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300': !item.is_target && item.level === 'province',
                                        'bg-blue-100 font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': !item.is_target && item.level === 'city',
                                        'bg-emerald-100 font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': !item.is_target && item.level === 'district',
                                        'bg-rose-100 font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300': !item.is_target && item.level === 'village',
                                    }"
                                    x-text="item.label"
                                ></span>
                            </template>
                        </div>
                    </td>

                    {{-- 5. Bidang & Tahap --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center text-xs dark:border-neutral-800">
                        <div class="flex flex-col items-center justify-center gap-1">
                            <span
                                class="inline-flex items-center rounded-md border px-2.5 py-1 text-[11px] font-medium leading-none"
                                :class="{
                                    'border-sky-200 bg-sky-50/80 text-sky-700 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300': row.bidang === 'Operasional',
                                    'border-emerald-200 bg-emerald-50/80 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300': row.bidang === 'Manajemen Anggaran dan Keuangan',
                                    'border-amber-200 bg-amber-50/80 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300': row.bidang === 'Logistik',
                                    'border-purple-200 bg-purple-50/80 text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300': row.bidang === 'Sumber Daya Manusia',
                                    'border-rose-200 bg-rose-50/80 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300': row.bidang === 'Investigasi',
                                }"
                                x-text="row.bidang"
                            ></span>
                            <template x-if="row.tahap">
                                <span
                                    class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-medium"
                                    :class="{
                                        'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300': String(row.tahap) === '1',
                                        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300': String(row.tahap) === '2',
                                        'border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800/60 dark:bg-teal-950/40 dark:text-teal-300': String(row.tahap) === '3',
                                        'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300': String(row.tahap) === 'Khusus',
                                    }"
                                    x-text="row.tahap === 'Khusus' ? 'Khusus' : 'Tahap ' + row.tahap"
                                ></span>
                            </template>
                        </div>
                    </td>

                    {{-- 6. Reviu & Validasi (Katim, Dalnis, Daltu bertumpuk) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                        <div class="flex flex-col items-center justify-center gap-1.5">
                            <template x-for="rt in reviewTypes" :key="rt.key">
                                <div class="inline-flex flex-col items-center">
                                    <template x-if="canReview(row, rt.key) && !reviewStatus(row, rt.key)">
                                        <button
                                            type="button"
                                            x-on:click="openReviewModal(row, rt.key)"
                                            class="inline-flex w-32 items-center justify-center gap-1.5 rounded-md bg-red-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-xs transition-colors hover:bg-red-700"
                                            :title="'Belum direviu ' + rt.label + ' - Klik untuk melakukan reviu'"
                                        >
                                            <x-lucide-clipboard-check class="h-3 w-3 shrink-0" />
                                            <span x-text="'Reviu ' + rt.label"></span>
                                        </button>
                                    </template>

                                    <template x-if="!canReview(row, rt.key) && !reviewStatus(row, rt.key)">
                                        <button
                                            type="button"
                                            x-on:click="openReviewModal(row, rt.key)"
                                            class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-[11px] font-medium text-neutral-600 transition-colors hover:bg-neutral-200 focus:outline-none dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700"
                                            :title="'Klik untuk melihat lembar reviu ' + rt.label"
                                            x-text="rt.label + ': Menunggu'"
                                        ></button>
                                    </template>

                                    <template x-if="reviewStatus(row, rt.key)">
                                        <button
                                            type="button"
                                            x-on:click="openReviewModal(row, rt.key)"
                                            class="group inline-flex cursor-pointer flex-col items-center rounded-md px-1.5 py-0.5 text-center transition-colors hover:bg-neutral-100/80 focus:outline-none dark:hover:bg-neutral-800/60"
                                            :title="canReview(row, rt.key) ? 'Klik untuk melihat riwayat atau reviu ulang ' + rt.label : 'Klik untuk melihat hasil reviu ' + rt.label"
                                        >
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-medium"
                                                :class="reviewStatus(row, rt.key) === 'Disetujui'
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300'"
                                            >
                                                <span x-text="rt.label + ': ' + reviewStatus(row, rt.key)"></span>
                                                <template x-if="canReview(row, rt.key)">
                                                    <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                                </template>
                                            </span>
                                            <template x-if="reviewDate(row, rt.key)">
                                                <span class="mt-0.5 text-[10px] font-medium text-neutral-500 dark:text-neutral-400" x-text="reviewDate(row, rt.key)"></span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </td>

                    {{-- 7. Tabulasi --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center dark:border-neutral-800">
                        <a
                            :href="row.tabulasi_url || (`/audit/kka/${row.id}/tabulasi`)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-neutral-700 shadow-2xs hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                            title="Tabulasi KKA"
                        >
                            <x-lucide-table-2 class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                            <span>Tabulasi (<span x-text="row.tabulasi_count ?? 0"></span>)</span>
                        </a>
                    </td>

                    {{-- 8. Status (toggle) & Aksi --}}
                    <td class="whitespace-nowrap px-3 py-2 text-center">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="inline-flex items-center justify-center gap-2 text-xs">
                                <button
                                    type="button"
                                    x-on:click.stop="toggleRowStatus(row, 'Draft')"
                                    class="cursor-pointer select-none text-[11px] transition-colors"
                                    :class="row.status !== 'Final' ? 'font-bold text-amber-600 dark:text-amber-400' : 'font-medium text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    title="Klik untuk mengubah status menjadi Draft"
                                >Draft</button>

                                <button
                                    type="button"
                                    x-on:click.stop="toggleRowStatus(row)"
                                    x-bind:disabled="row.updatingStatus === true"
                                    role="switch"
                                    :aria-checked="row.status === 'Final'"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:opacity-50"
                                    :class="row.status === 'Final' ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700'"
                                    :title="row.status === 'Final' ? 'Klik untuk beralih ke Draft' : 'Klik untuk beralih ke Final'"
                                >
                                    <span
                                        class="pointer-events-none inline-flex h-4 w-4 transform items-center justify-center rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                        :class="row.status === 'Final' ? 'translate-x-4' : 'translate-x-0'"
                                    >
                                        <template x-if="row.updatingStatus">
                                            <span class="h-2.5 w-2.5 animate-spin rounded-full border border-neutral-400 border-t-neutral-800"></span>
                                        </template>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    x-on:click.stop="toggleRowStatus(row, 'Final')"
                                    class="cursor-pointer select-none text-[11px] transition-colors"
                                    :class="row.status === 'Final' ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'font-medium text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    title="Klik untuk mengubah status menjadi Final"
                                >Final</button>
                            </div>

                            <div class="inline-flex items-center justify-center gap-1">
                                <a
                                    :href="row.print_url || `${baseUrl}/${row.id}/print`"
                                    target="_blank"
                                    class="{{ $rowButtonClass }}"
                                    title="Cetak / Print PDF KKA"
                                >
                                    <x-lucide-printer class="h-3.5 w-3.5" />
                                </a>
                                <button type="button" x-on:click="confirmDuplicate(row)" class="{{ $rowButtonClass }}" title="Duplikasi KKA">
                                    <x-lucide-copy class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" x-on:click="openEditModal(row)" class="{{ $rowButtonClass }}" title="Ubah KKA">
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    x-on:click="confirmDelete(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Hapus KKA"
                                >
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit KKA --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div x-show="modalOpen" x-transition.opacity x-on:click="closeModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

            <div
                x-show="modalOpen"
                x-transition
                class="relative z-10 my-6 w-full max-w-5xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Kertas Kerja Audit (KKA)' : 'Tambah Kertas Kerja Audit (KKA)'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Pilih Nomor PKA, lengkapi data pelaksanaan, auditee, serta tujuan, langkah, dan hasil KKA.
                        </p>
                    </div>
                    <button type="button" x-on:click="closeModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                {{-- Modal Form --}}
                <form x-on:submit.prevent="saveKka()" class="flex max-h-[85vh] flex-col">
                    <div x-ref="modalScrollArea" class="space-y-6 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        {{-- Metadata Utama --}}
                        <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                                Metadata Kertas Kerja Audit
                            </h3>

                            <div class="space-y-4">
                                {{-- Nomor KKA --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        Nomor KKA <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="form.nomor_kka"
                                        placeholder="Contoh: KKA/001/WIL-I/2026..."
                                        class="{{ $inputClass }}"
                                    />
                                    <template x-if="errors.nomor_kka">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nomor_kka[0]"></p>
                                    </template>
                                </div>

                                {{-- 1. Nomor PKA --}}
                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="1. Nomor PKA *"
                                        placeholder="Pilih Nomor PKA..."
                                        itemsExpr="pkaOptions"
                                        valueModel="form.pka_id"
                                        changeAction="onPkaChange()"
                                    />
                                    <template x-if="errors.pka_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.pka_id[0]"></p>
                                    </template>
                                </div>

                                {{-- 2. Data otomatis dari PKA + Tanggal (3 Baris & Lurus Atas Bawah) --}}
                                <div class="space-y-3 rounded-xl border border-dashed border-neutral-300 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-950">
                                    {{-- Row 1: Nomor Sprin, Bidang, Jenis Audit --}}
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div class="space-y-1">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Nomor Sprin</p>
                                            <div class="{{ $readonlyClass }}" x-text="selectedPka ? selectedPka.no_sprin : '-'"></div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Bidang</p>
                                            <div class="{{ $readonlyClass }}" x-text="selectedPka ? selectedPka.bidang : '-'"></div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Jenis Audit</p>
                                            <div class="{{ $readonlyClass }}" x-text="selectedPka ? selectedPka.jenis_audit : '-'"></div>
                                        </div>
                                    </div>

                                    {{-- Row 2: Dibuat Oleh & Direviu Oleh (2 kolom) --}}
                                    <div class="grid gap-3 border-t border-neutral-100 pt-3 sm:grid-cols-2 dark:border-neutral-800">
                                        <div class="space-y-1">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Dibuat Oleh</p>
                                            <div class="{{ $readonlyClass }}" x-text="selectedPka ? (selectedPka.dibuat_oleh_name || '-') : '-'"></div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Direviu Oleh</p>
                                            <div class="{{ $readonlyClass }}" x-text="selectedPka ? (selectedPka.direviu_oleh_name || '-') : '-'"></div>
                                        </div>
                                    </div>

                                    {{-- Row 3: Tanggal Dibuat Oleh & Tanggal Direviu Oleh (2 kolom lurus dengan Dibuat Oleh & Direviu Oleh di atasnya) --}}
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">3. Tanggal Dibuat Oleh</label>
                                            <input type="date" x-model="form.tanggal_dibuat" class="{{ $inputClass }}" />
                                            <template x-if="errors.tanggal_dibuat">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tanggal_dibuat[0]"></p>
                                            </template>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">4. Tanggal Direviu Oleh</label>
                                            <input type="date" x-model="form.tanggal_direviu" class="{{ $inputClass }}" />
                                            <template x-if="errors.tanggal_direviu">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tanggal_direviu[0]"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- 5. Tanggal Pembuatan KKA & 6. Tempat --}}
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="space-y-1.5 sm:col-span-1">
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            5. Tanggal Pembuatan KKA <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" x-model="form.tanggal_kka" class="{{ $inputClass }}" />
                                        <template x-if="errors.tanggal_kka">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tanggal_kka[0]"></p>
                                        </template>
                                    </div>

                                    <div class="space-y-1.5 sm:col-span-2">
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            6. Tempat <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="form.tempat" placeholder="Contoh: Jakarta" class="{{ $inputClass }}" />
                                        <template x-if="errors.tempat">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tempat[0]"></p>
                                        </template>
                                    </div>
                                </div>

                                {{-- 7. Auditee --}}
                                <div class="space-y-3">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            7. Auditee - Level <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid gap-1.5 sm:w-72" :class="levels.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
                                            <template x-for="lv in levels" :key="lv">
                                                <button
                                                    type="button"
                                                    x-on:click="form.auditee_level = lv; onLevelChange()"
                                                    class="h-9 rounded-md border px-3 text-xs font-medium transition-colors"
                                                    :class="form.auditee_level === lv
                                                        ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                                                        : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                                    x-text="lv"
                                                ></button>
                                            </template>
                                        </div>
                                        <template x-if="errors.auditee_level">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.auditee_level[0]"></p>
                                        </template>
                                    </div>

                                    <template x-if="form.auditee_level === 'Nasional'">
                                        <div class="space-y-1.5 sm:w-72">
                                            <x-searchable-select
                                                label="WIL Auditee *"
                                                placeholder="Pilih WIL..."
                                                itemsExpr="plottingOptions"
                                                valueModel="form.auditee_plotting_id"
                                                changeAction=""
                                            />
                                            <template x-if="errors.auditee_plotting_id">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.auditee_plotting_id[0]"></p>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="form.auditee_level === 'Wilayah'">
                                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                            <div class="space-y-1.5">
                                                <x-searchable-select
                                                    label="Provinsi *"
                                                    placeholder="Pilih Provinsi..."
                                                    itemsExpr="provinceOptions"
                                                    valueModel="form.province_code"
                                                    changeAction="onProvinceChange()"
                                                />
                                                <template x-if="errors.province_code">
                                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.province_code[0]"></p>
                                                </template>
                                            </div>
                                            <div class="space-y-1.5">
                                                <x-searchable-select
                                                    label="Kabupaten / Kota (opsional)"
                                                    placeholder="Pilih Kabupaten / Kota..."
                                                    itemsExpr="cityOptions"
                                                    valueModel="form.city_code"
                                                    changeAction="onCityChange()"
                                                    disabledExpr="!form.province_code"
                                                    disabledMessage="Pilih Provinsi terlebih dahulu"
                                                />
                                                <template x-if="errors.city_code">
                                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.city_code[0]"></p>
                                                </template>
                                            </div>
                                            <div class="space-y-1.5">
                                                <x-searchable-select
                                                    label="Kecamatan (opsional)"
                                                    placeholder="Pilih Kecamatan..."
                                                    itemsExpr="districtOptions"
                                                    valueModel="form.district_code"
                                                    changeAction="onDistrictChange()"
                                                    disabledExpr="!form.city_code"
                                                    disabledMessage="Pilih Kabupaten / Kota terlebih dahulu"
                                                />
                                                <template x-if="errors.district_code">
                                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.district_code[0]"></p>
                                                </template>
                                            </div>
                                            <div class="space-y-1.5">
                                                <x-searchable-select
                                                    label="Desa / Kelurahan (opsional)"
                                                    placeholder="Pilih Desa / Kelurahan..."
                                                    itemsExpr="villageOptions"
                                                    valueModel="form.village_code"
                                                    changeAction=""
                                                    disabledExpr="!form.district_code"
                                                    disabledMessage="Pilih Kecamatan terlebih dahulu"
                                                />
                                                <template x-if="errors.village_code">
                                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.village_code[0]"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 8. Template KKA (opsional) --}}
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 dark:border-indigo-950/60 dark:bg-indigo-950/20">
                            <x-searchable-select
                                label="8. Template KKA (opsional)"
                                placeholder="-- Cari dan Pilih Template KKA --"
                                itemsExpr="templateOptions"
                                valueModel="form.template_kka_id"
                                changeAction="onTemplateChange()"
                            />
                            <p class="mt-1.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                                Jika dipilih, Tujuan, Langkah, dan Hasil akan terisi otomatis sesuai template (isian yang sudah ada akan diganti).
                            </p>
                            <template x-if="errors.template_kka_id">
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors.template_kka_id[0]"></p>
                            </template>
                        </div>

                        {{-- 9 - 11. Tujuan, Langkah, Hasil --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                                    Isian Dokumen KKA (Format TipTap Rich Text)
                                </label>
                                <span class="text-[11px] text-neutral-400 dark:text-neutral-500">Pilih tab untuk menyunting kontennya</span>
                            </div>

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

                            <div class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                <x-tiptap-toolbar />

                                @foreach ([
                                    'tujuan' => '9. Tujuan',
                                    'langkah' => '10. Langkah',
                                    'hasil' => '11. Hasil',
                                ] as $key => $label)
                                    <div x-show="activeTab === '{{ $key }}'" class="flex flex-col">
                                        <div
                                            x-ref="editor_{{ $key }}"
                                            x-on:click="focusActiveEditor($event)"
                                            class="tiptap max-h-[460px] min-h-[280px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
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

                        {{-- 12. Status --}}
                        <div class="rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <label class="mb-2 block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                12. Status KKA <span class="text-red-500">*</span>
                            </label>
                            <div class="inline-flex items-center gap-3">
                                <span
                                    class="cursor-pointer select-none text-xs transition-colors"
                                    :class="form.status !== 'Final' ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    x-on:click="form.status = 'Draft'"
                                >Draft</span>

                                <button
                                    type="button"
                                    x-on:click="form.status = (form.status === 'Final' ? 'Draft' : 'Final')"
                                    role="switch"
                                    :aria-checked="form.status === 'Final'"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    :class="form.status === 'Final' ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700'"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                        :class="form.status === 'Final' ? 'translate-x-5' : 'translate-x-0'"
                                    ></span>
                                </button>

                                <span
                                    class="cursor-pointer select-none text-xs transition-colors"
                                    :class="form.status === 'Final' ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    x-on:click="form.status = 'Final'"
                                >Final</span>
                            </div>
                            <template x-if="errors.status">
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors.status[0]"></p>
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
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        >
                            <template x-if="saving">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                            </template>
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan KKA')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Reviu KKA (Katim, Dalnis & Daltu) --}}
        <div
            x-show="reviewModalOpen"
            x-cloak
            x-on:keydown.escape.window="closeReviewModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div x-show="reviewModalOpen" x-transition.opacity x-on:click="closeReviewModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

            <div
                x-show="reviewModalOpen"
                x-transition
                class="relative z-10 my-8 w-full max-w-4xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                            <x-lucide-clipboard-check class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="'Lembar Reviu KKA — ' + reviewTypeLabel()"></h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400" x-text="'Pemeriksaan dan persetujuan ' + (reviewKka?.role_label || 'Reviewer') + '.'"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeReviewModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <div class="max-h-[80vh] space-y-6 overflow-y-auto px-6 py-5">
                    {{-- Ringkasan KKA --}}
                    <div class="rounded-xl border border-neutral-200 bg-neutral-50/60 p-4 dark:border-neutral-800 dark:bg-neutral-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 pb-3 dark:border-neutral-800">
                            <div class="space-y-0.5">
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Nomor KKA, PKA & Sprin</span>
                                <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100" x-text="reviewKka?.nomor_kka || '-'"></h3>
                                <p class="text-xs font-medium text-amber-600 dark:text-amber-400" x-text="'PKA: ' + (reviewKka?.nomor_pka || '-')"></p>
                                <p class="text-xs font-medium text-red-600 dark:text-red-400" x-text="'Sprin: ' + (reviewKka?.no_sprin || '-')"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-neutral-500">Status Reviu Saat Ini:</span>
                                <template x-if="reviewKka?.latest_review_status === 'Disetujui'">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Disetujui</span>
                                </template>
                                <template x-if="reviewKka?.latest_review_status === 'Koreksi'">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">Koreksi</span>
                                </template>
                                <template x-if="!reviewKka?.latest_review_status">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Menunggu Reviu</span>
                                </template>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Auditor</span>
                                <p class="mt-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200" x-text="reviewKka?.auditor_name || '-'"></p>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Bidang & Jenis Audit</span>
                                <p class="mt-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200" x-text="reviewKka?.bidang || '-'"></p>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="reviewKka?.jenis_audit || '-'"></p>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Tanggal & Tempat KKA</span>
                                <p class="mt-1 text-xs font-medium text-neutral-800 dark:text-neutral-200" x-text="reviewKka?.tanggal_kka_formatted || '-'"></p>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 break-words" x-text="reviewKka?.tempat || '-'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Log Reviu --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Log Riwayat Reviu</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="'Catatan dan status hasil telaah oleh ' + (reviewKka?.role_label || 'Reviewer') + '.'"></p>
                            </div>
                            <template x-if="reviewKka?.can_review">
                                <button
                                    type="button"
                                    x-on:click="reviewFormOpen = !reviewFormOpen"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-neutral-900 px-3 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                                >
                                    <x-lucide-plus x-show="!reviewFormOpen" class="h-3.5 w-3.5" />
                                    <x-lucide-x x-show="reviewFormOpen" x-cloak class="h-3.5 w-3.5" />
                                    <span x-text="reviewFormOpen ? 'Tutup Form' : 'Tambah Reviu'"></span>
                                </button>
                            </template>
                        </div>

                        <template x-if="reviewKka?.can_review">
                            <div x-show="reviewFormOpen" x-cloak x-transition class="rounded-xl border border-neutral-300 bg-neutral-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-900/60">
                                <form x-on:submit.prevent="saveReview()" class="space-y-3">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            Status Reviu <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-2 sm:w-72">
                                            <label
                                                class="flex cursor-pointer items-center justify-center gap-1.5 rounded-md border p-2 text-xs font-medium transition-colors"
                                                :class="reviewForm.status === 'Disetujui'
                                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600 dark:border-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-300'
                                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300'"
                                            >
                                                <input type="radio" x-model="reviewForm.status" value="Disetujui" class="sr-only" />
                                                <x-lucide-check-circle class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                                <span>Disetujui</span>
                                            </label>
                                            <label
                                                class="flex cursor-pointer items-center justify-center gap-1.5 rounded-md border p-2 text-xs font-medium transition-colors"
                                                :class="reviewForm.status === 'Koreksi'
                                                    ? 'border-amber-500 bg-amber-50 text-amber-800 ring-1 ring-amber-500 dark:border-amber-500 dark:bg-amber-950/50 dark:text-amber-300'
                                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300'"
                                            >
                                                <input type="radio" x-model="reviewForm.status" value="Koreksi" class="sr-only" />
                                                <x-lucide-alert-circle class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                                <span>Koreksi</span>
                                            </label>
                                        </div>
                                        <template x-if="reviewErrors.status">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="reviewErrors.status[0]"></p>
                                        </template>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Catatan Reviu</label>
                                        <textarea
                                            x-model="reviewForm.catatan"
                                            rows="3"
                                            placeholder="Tuliskan catatan, rekomendasi, atau instruksi perbaikan..."
                                            class="block w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100"
                                        ></textarea>
                                        <template x-if="reviewErrors.catatan">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="reviewErrors.catatan[0]"></p>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button
                                            type="button"
                                            x-on:click="reviewFormOpen = false"
                                            class="inline-flex h-8 items-center rounded-md border border-neutral-200 bg-white px-3 text-xs font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300"
                                        >Batal</button>
                                        <button
                                            type="submit"
                                            x-bind:disabled="savingReview"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-md bg-neutral-900 px-3 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                                        >
                                            <x-lucide-loader-circle x-show="savingReview" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                            <span>Simpan Reviu</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </template>

                        <div class="relative overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-800">
                            <div x-show="loadingReviews" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-[1px] dark:bg-neutral-950/70">
                                <div class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                                    <x-lucide-loader-circle class="h-3.5 w-3.5 animate-spin" />
                                    <span>Memuat riwayat reviu...</span>
                                </div>
                            </div>

                            <table class="w-full border-collapse text-left text-xs">
                                <thead>
                                    <tr class="border-b border-neutral-200 bg-neutral-50/70 text-[11px] uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400">
                                        <th class="w-10 whitespace-nowrap px-3 py-2.5 text-center font-medium">#</th>
                                        <th class="w-36 whitespace-nowrap px-3 py-2.5 font-medium">WAKTU</th>
                                        <th class="w-40 whitespace-nowrap px-3 py-2.5 font-medium">REVIEWER</th>
                                        <th class="w-28 whitespace-nowrap px-3 py-2.5 text-center font-medium">STATUS</th>
                                        <th class="px-3 py-2.5 font-medium">CATATAN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    <template x-for="(log, idx) in reviewLogs" :key="log.id">
                                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-neutral-900/30">
                                            <td class="whitespace-nowrap px-3 py-2.5 text-center text-neutral-500" x-text="idx + 1"></td>
                                            <td class="whitespace-nowrap px-3 py-2.5 text-neutral-700 dark:text-neutral-300" x-text="log.created_at"></td>
                                            <td class="whitespace-nowrap px-3 py-2.5 font-medium text-neutral-900 dark:text-neutral-100" x-text="log.reviewer_name"></td>
                                            <td class="whitespace-nowrap px-3 py-2.5 text-center">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                    :class="log.status === 'Disetujui'
                                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300'"
                                                    x-text="log.status"
                                                ></span>
                                            </td>
                                            <td class="break-words px-3 py-2.5 text-neutral-800 dark:text-neutral-200" x-text="log.catatan || '-'"></td>
                                        </tr>
                                    </template>

                                    <template x-if="!loadingReviews && reviewLogs.length === 0">
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                                Belum ada riwayat reviu untuk KKA ini.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-neutral-200 px-6 py-3.5 dark:border-neutral-800">
                    <button
                        type="button"
                        x-on:click="closeReviewModal()"
                        class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
