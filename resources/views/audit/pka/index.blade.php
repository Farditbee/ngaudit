<x-admin-layout>
    <x-slot:title>Program Kerja Audit (PKA)</x-slot:title>

    <div
        x-data="pkaDataTable({{ Js::from($dataTableConfig) }})"
        class="space-y-6"
    >
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                        Program Kerja Audit (PKA)
                    </h1>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Penyusunan dan pengelolaan Program Kerja Audit berpedoman pada Surat Perintah (Sprin) dan template standar.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    x-on:click="openCreateModal()"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-3.5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    <x-lucide-plus class="h-4 w-4" />
                    <span>Tambah PKA</span>
                </button>
            </div>
        </div>



        {{-- Filters Bar --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Filter Bidang --}}
            <div>
                <label class="block mb-1 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Bidang</label>
                <select
                    x-model="filterBidang"
                    x-on:change="onFilterChange()"
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-800 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                >
                    <option value="">Semua Bidang</option>
                    <template x-for="b in bidangs" :key="b">
                        <option :value="b" x-text="b"></option>
                    </template>
                </select>
            </div>

            {{-- Filter Jenis Audit --}}
            <div>
                <label class="block mb-1 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Jenis Audit</label>
                <select
                    x-model="filterJenisAudit"
                    x-on:change="onFilterChange()"
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-800 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                >
                    <option value="">Semua Jenis Audit</option>
                    <template x-for="ja in jenisAudits" :key="ja">
                        <option :value="ja" x-text="ja"></option>
                    </template>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block mb-1 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">Filter Status</label>
                <select
                    x-model="filterStatus"
                    x-on:change="onFilterChange()"
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-800 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                >
                    <option value="">Semua Status</option>
                    <template x-for="st in statuses" :key="st">
                        <option :value="st" x-text="st"></option>
                    </template>
                </select>
            </div>

            {{-- Reset Filters --}}
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

        {{-- DataTable Component --}}
        <x-data-table
            :columns="$columns"
            search-placeholder="Cari nomor PKA, sprin, sasaran auditi..."
            :page-length="10"
        >
            <template x-for="(row, index) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-50/70 dark:border-neutral-800 dark:hover:bg-neutral-900/50"
                    :class="index % 2 === 1 ? 'bg-neutral-50/40 dark:bg-neutral-900/20' : ''"
                >
                    {{-- 1. # --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 2. Nomor SPRIN & Nomor PKA --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-xs dark:border-neutral-800">
                        <div class="space-y-1">
                            {{-- Baris SPRIN: warna danger (merah) + underline --}}
                            <div>
                                <span class="font-medium text-red-600 underline underline-offset-2 decoration-red-400 dark:text-red-400 dark:decoration-red-500" x-text="row.no_sprin"></span>
                            </div>

                            {{-- Baris PKA: warna warning (amber) + underline --}}
                            <div>
                                <span class="font-medium text-amber-600 underline underline-offset-2 decoration-amber-400 dark:text-amber-400 dark:decoration-amber-500" x-text="row.nomor_pka"></span>
                            </div>
                        </div>
                    </td>

                    {{-- 3. Tipe & Tahap --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center text-xs dark:border-neutral-800">
                        <div class="flex flex-col items-center justify-center gap-1">
                            <template x-if="row.sprin_tipe">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="row.sprin_tipe === 'Nasional'
                                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                    x-text="row.sprin_tipe"
                                ></span>
                            </template>
                            <template x-if="row.tahap">
                                <span
                                    class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-medium"
                                    :class="{
                                        'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300': String(row.tahap) === '1',
                                        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300': String(row.tahap) === '2',
                                        'border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800/60 dark:bg-teal-950/40 dark:text-teal-300': String(row.tahap) === '3',
                                        'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300': String(row.tahap) === 'Khusus',
                                        'border-neutral-200 bg-neutral-50 text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900/60 dark:text-neutral-400': !['1', '2', '3', 'Khusus'].includes(String(row.tahap)),
                                    }"
                                    x-text="row.tahap === 'Khusus' ? 'Khusus' : 'Tahap ' + row.tahap"
                                ></span>
                            </template>
                        </div>
                    </td>

                    {{-- 4. Bidang --}}
                    <td class="border-r border-neutral-200 px-4 py-3 text-center text-xs dark:border-neutral-800">
                        <span
                            class="inline-flex items-center rounded-md border px-2.5 py-1 text-[11px] font-medium leading-none"
                            :class="{
                                'border-sky-200 bg-sky-50/80 text-sky-700 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300': row.bidang === 'Operasional',
                                'border-emerald-200 bg-emerald-50/80 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300': row.bidang === 'Manajemen Anggaran dan Keuangan',
                                'border-amber-200 bg-amber-50/80 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300': row.bidang === 'Logistik',
                                'border-purple-200 bg-purple-50/80 text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300': row.bidang === 'Sumber Daya Manusia',
                                'border-rose-200 bg-rose-50/80 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300': row.bidang === 'Investigasi',
                                'border-neutral-200 bg-neutral-50/80 text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300': !['Operasional', 'Manajemen Anggaran dan Keuangan', 'Logistik', 'Sumber Daya Manusia', 'Investigasi'].includes(row.bidang),
                            }"
                            x-text="row.bidang"
                        ></span>
                    </td>

                    {{-- 5. Jenis --}}
                    <td class="border-r border-neutral-200 px-2.5 py-3 text-center text-xs dark:border-neutral-800">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium leading-tight"
                            :class="{
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': row.jenis_audit === 'Audit Kinerja',
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300': row.jenis_audit === 'Audit PNBP',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': row.jenis_audit === 'Audit Tujuan Tertentu',
                            }"
                            x-text="row.jenis_audit"
                        ></span>
                    </td>

                    {{-- 6. Status (Toggle Switch Langsung di Datatable: Draft di kiri, Final di kanan) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-xs dark:border-neutral-800">
                        <div class="inline-flex items-center justify-center gap-2">
                            {{-- Label Draft: Klik langsung ubah ke Draft --}}
                            <button
                                type="button"
                                x-on:click.stop="toggleRowStatus(row, 'Draft')"
                                class="text-[11px] select-none cursor-pointer transition-colors"
                                :class="row.status !== 'Final' ? 'font-bold text-amber-600 dark:text-amber-400' : 'font-medium text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                title="Klik untuk mengubah status menjadi Draft"
                            >
                                Draft
                            </button>

                            {{-- Switch Button: Klik langsung toggle bolak-balik --}}
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
                                    class="pointer-events-none inline-flex items-center justify-center h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                    :class="row.status === 'Final' ? 'translate-x-4' : 'translate-x-0'"
                                >
                                    <template x-if="row.updatingStatus">
                                        <span class="h-2.5 w-2.5 animate-spin rounded-full border border-neutral-400 border-t-neutral-800"></span>
                                    </template>
                                </span>
                            </button>

                            {{-- Label Final: Klik langsung ubah ke Final --}}
                            <button
                                type="button"
                                x-on:click.stop="toggleRowStatus(row, 'Final')"
                                class="text-[11px] select-none cursor-pointer transition-colors"
                                :class="row.status === 'Final' ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'font-medium text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                title="Klik untuk mengubah status menjadi Final"
                            >
                                Final
                            </button>
                        </div>
                    </td>

                    {{-- 7. KKA (Button dan label jumlah KKA) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center dark:border-neutral-800">
                        <a
                            href="#"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-neutral-700 shadow-2xs hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                            title="Kertas Kerja Audit (KKA)"
                        >
                            <x-lucide-file-spreadsheet class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                            <span>KKA</span>
                            <span class="inline-flex h-4 min-w-[1.25rem] items-center justify-center rounded-full bg-neutral-100 px-1.5 text-[10px] font-bold text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200" x-text="row.kka_count ?? 0">0</span>
                        </a>
                    </td>

                    {{-- 8. Reviu Dalnis & Daltu (1 Kolom: Dalnis di atas, Daltu di bawah) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">
                        <div class="flex flex-col items-center justify-center gap-1.5">
                            {{-- Baris Atas: Reviu Dalnis --}}
                            <div class="inline-flex flex-col items-center">
                                <template x-if="row.can_review_dalnis && !row.latest_dalnis_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'dalnis')"
                                        class="inline-flex w-32 items-center justify-center gap-1.5 rounded-md bg-red-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-xs transition-colors hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
                                        title="Belum direviu Dalnis - Klik untuk melakukan reviu"
                                    >
                                        <x-lucide-clipboard-check class="h-3 w-3 shrink-0" />
                                        <span>Reviu Dalnis</span>
                                    </button>
                                </template>

                                <template x-if="!row.can_review_dalnis && !row.latest_dalnis_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'dalnis')"
                                        class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-[11px] font-medium text-neutral-600 transition-colors hover:bg-neutral-200 focus:outline-none dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700"
                                        title="Klik untuk melihat lembar reviu Dalnis"
                                    >
                                        Dalnis: Menunggu
                                    </button>
                                </template>

                                <template x-if="row.latest_dalnis_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'dalnis')"
                                        class="group inline-flex flex-col items-center text-center focus:outline-none cursor-pointer rounded-md px-1.5 py-0.5 transition-colors hover:bg-neutral-100/80 dark:hover:bg-neutral-800/60"
                                        :title="row.can_review_dalnis ? 'Klik untuk melihat riwayat atau reviu ulang Dalnis' : 'Klik untuk melihat hasil reviu Dalnis'"
                                    >
                                        <template x-if="row.latest_dalnis_review_status === 'Disetujui'">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                <span>Dalnis: Disetujui</span>
                                                <template x-if="row.can_review_dalnis">
                                                    <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                                </template>
                                            </span>
                                        </template>
                                        <template x-if="row.latest_dalnis_review_status === 'Koreksi'">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                                <span>Dalnis: Koreksi</span>
                                                <template x-if="row.can_review_dalnis">
                                                    <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                                </template>
                                            </span>
                                        </template>

                                        <template x-if="row.latest_dalnis_review_date">
                                            <span class="mt-0.5 text-[10px] font-medium text-neutral-500 group-hover:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-200" x-text="row.latest_dalnis_review_date"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>

                            {{-- Baris Bawah: Reviu Daltu --}}
                            <div class="inline-flex flex-col items-center">
                                <template x-if="row.can_review_daltu && !row.latest_daltu_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'daltu')"
                                        class="inline-flex w-32 items-center justify-center gap-1.5 rounded-md bg-red-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-xs transition-colors hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
                                        title="Belum direviu Daltu - Klik untuk melakukan reviu"
                                    >
                                        <x-lucide-clipboard-check class="h-3 w-3 shrink-0" />
                                        <span>Reviu Daltu</span>
                                    </button>
                                </template>

                                <template x-if="!row.can_review_daltu && !row.latest_daltu_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'daltu')"
                                        class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-[11px] font-medium text-neutral-600 transition-colors hover:bg-neutral-200 focus:outline-none dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700"
                                        title="Klik untuk melihat lembar reviu Daltu"
                                    >
                                        Daltu: Menunggu
                                    </button>
                                </template>

                                <template x-if="row.latest_daltu_review_status">
                                    <button
                                        type="button"
                                        x-on:click="openReviewModal(row, 'daltu')"
                                        class="group inline-flex flex-col items-center text-center focus:outline-none cursor-pointer rounded-md px-1.5 py-0.5 transition-colors hover:bg-neutral-100/80 dark:hover:bg-neutral-800/60"
                                        :title="row.can_review_daltu ? 'Klik untuk melihat riwayat atau reviu ulang Daltu' : 'Klik untuk melihat hasil reviu Daltu'"
                                    >
                                        <template x-if="row.latest_daltu_review_status === 'Disetujui'">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                <span>Daltu: Disetujui</span>
                                                <template x-if="row.can_review_daltu">
                                                    <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                                </template>
                                            </span>
                                        </template>
                                        <template x-if="row.latest_daltu_review_status === 'Koreksi'">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                                <span>Daltu: Koreksi</span>
                                                <template x-if="row.can_review_daltu">
                                                    <x-lucide-pencil class="h-2.5 w-2.5 opacity-60 group-hover:opacity-100" />
                                                </template>
                                            </span>
                                        </template>

                                        <template x-if="row.latest_daltu_review_date">
                                            <span class="mt-0.5 text-[10px] font-medium text-neutral-500 group-hover:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-200" x-text="row.latest_daltu_review_date"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </td>

                    {{-- 9. Tanggal (Di sebelah kanan kolom Reviu) --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center text-xs dark:border-neutral-800 text-neutral-600 dark:text-neutral-400">
                        <span x-text="row.tanggal_formatted"></span>
                    </td>

                    {{-- 10. Aksi (3 di atas, 2 di bawah) --}}
                    <td class="whitespace-nowrap px-3 py-2 text-center">
                        <div class="flex flex-col items-center justify-center gap-1">
                            {{-- Baris 1: 3 tombol (Print, Duplikasi, Langkah Kerja) --}}
                            <div class="inline-flex items-center justify-center gap-1">
                                <a
                                    :href="row.print_url || `${baseUrl}/${row.id}/print`"
                                    target="_blank"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Cetak / Print PDF PKA"
                                >
                                    <x-lucide-printer class="h-3.5 w-3.5" />
                                </a>
                                <button
                                    type="button"
                                    x-on:click="openDuplicateModal(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Duplikasi PKA"
                                >
                                    <x-lucide-copy class="h-3.5 w-3.5" />
                                </button>
                                <a
                                    :href="row.langkah_kerja_url || `${baseUrl}/${row.id}/langkah-kerja`"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Langkah Kerja"
                                >
                                    <x-lucide-list-checks class="h-3.5 w-3.5" />
                                </a>
                            </div>

                            {{-- Baris 2: 2 tombol (Ubah, Hapus) --}}
                            <div class="inline-flex items-center justify-center gap-1">
                                <button
                                    type="button"
                                    x-on:click="openEditModal(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                    title="Ubah PKA"
                                >
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    x-on:click="confirmDelete(row)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                    title="Hapus PKA"
                                >
                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit PKA --}}
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
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Program Kerja Audit (PKA)' : 'Tambah Program Kerja Audit (PKA)'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Lengkapi metadata audit dan bagian isian format dokumen PKA.
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
                <form x-on:submit.prevent="savePka()" class="flex flex-col max-h-[85vh]">
                    <div x-ref="modalScrollArea" class="space-y-6 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        {{-- Metadata Utama (Items 1 - 8) --}}
                        <div class="rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30 space-y-4">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                                Metadata Program Kerja Audit
                            </h3>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {{-- 1. Nomor Sprin (Full Width) --}}
                                <div class="space-y-1.5 sm:col-span-2 lg:col-span-3">
                                    <x-searchable-select
                                        label="1. Nomor Sprin *"
                                        placeholder="Pilih Nomor Sprin..."
                                        itemsExpr="sprinOptions"
                                        valueModel="form.sprin_id"
                                        changeAction="onSprinChange()"
                                    />
                                    <template x-if="errors.sprin_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sprin_id[0]"></p>
                                    </template>
                                </div>

                                {{-- Previu Susunan Tim dari Sprin yang dipilih --}}
                                <template x-if="selectedSprin">
                                    <div class="sm:col-span-2 lg:col-span-3 overflow-hidden rounded-xl border border-neutral-200 bg-white text-xs shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/80 px-4 py-2 dark:border-neutral-800 dark:bg-neutral-900/60">
                                            <div class="flex items-center gap-2">
                                                <x-lucide-users class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                                                <span class="font-semibold text-neutral-800 dark:text-neutral-200">Susunan Tim Sprin:</span>
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedSprin.tim_name || '-'"></span>
                                            </div>
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                :class="selectedSprin.tipe === 'Nasional'
                                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                                x-text="selectedSprin.tipe + (selectedSprin.scope_name ? ' - ' + selectedSprin.scope_name : '')"
                                            ></span>
                                        </div>

                                        <div class="grid divide-y divide-neutral-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:divide-neutral-800">
                                            <div class="p-3">
                                                <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Koordinator & Pengendali (Daltu)</p>
                                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100" x-text="selectedSprin.daltu_name || '-'"></p>
                                            </div>
                                            <div class="p-3">
                                                <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Pengawas Tim (Dalnis)</p>
                                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100" x-text="selectedSprin.dalnis_name || '-'"></p>
                                            </div>
                                            <div class="p-3">
                                                <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Ketua Tim (Katim)</p>
                                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100" x-text="(selectedSprin.katim_names && selectedSprin.katim_names.length > 0) ? selectedSprin.katim_names.join(', ') : '-'"></p>
                                            </div>
                                        </div>

                                        <div class="border-t border-neutral-200 p-3 dark:border-neutral-800">
                                            <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Anggota (Auditor)</p>
                                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                <template x-for="(audName, aIdx) in (selectedSprin.auditors || [])" :key="aIdx">
                                                    <span class="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-neutral-50 px-2.5 py-0.5 text-xs font-medium text-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                                                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-white text-[10px] font-bold text-neutral-600 shadow-2xs dark:bg-neutral-800 dark:text-neutral-400" x-text="aIdx + 1"></span>
                                                        <span x-text="audName"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!selectedSprin.auditors || selectedSprin.auditors.length === 0">
                                                    <span class="text-xs italic text-neutral-400">-</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- 2. Nomor PKA (Full Width) --}}
                                <div class="space-y-1.5 sm:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        2. Nomor PKA <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="form.nomor_pka"
                                        placeholder="Contoh: PKA/001/WIL-I/2026..."
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                    <template x-if="errors.nomor_pka">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nomor_pka[0]"></p>
                                    </template>
                                </div>

                                {{-- 3. Tanggal --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        3. Tanggal <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        x-model="form.tanggal"
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                    <template x-if="errors.tanggal">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tanggal[0]"></p>
                                    </template>
                                </div>

                                {{-- 4. Anggaran --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        4. Anggaran (Rp) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        x-model="form.anggaran"
                                        placeholder="Contoh: 50000000"
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                    <template x-if="errors.anggaran">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.anggaran[0]"></p>
                                    </template>
                                </div>

                                {{-- 5. Bidang --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        5. Bidang <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        x-model="form.bidang"
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    >
                                        <option value="">Pilih Bidang Audit...</option>
                                        <template x-for="b in bidangs" :key="b">
                                            <option :value="b" x-text="b"></option>
                                        </template>
                                    </select>
                                    <template x-if="errors.bidang">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.bidang[0]"></p>
                                    </template>
                                </div>

                                {{-- 6. Jenis Audit --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        6. Jenis Audit <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        x-model="form.jenis_audit"
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    >
                                        <option value="">Pilih Jenis Audit...</option>
                                        <template x-for="ja in jenisAudits" :key="ja">
                                            <option :value="ja" x-text="ja"></option>
                                        </template>
                                    </select>
                                    <template x-if="errors.jenis_audit">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.jenis_audit[0]"></p>
                                    </template>
                                </div>

                                {{-- 7. Sasaran Auditi --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        7. Sasaran Auditi <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="form.sasaran_auditi"
                                        placeholder="Contoh: Kantor Wilayah / Balai / Satuan Kerja..."
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                    <template x-if="errors.sasaran_auditi">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sasaran_auditi[0]"></p>
                                    </template>
                                </div>

                                {{-- 8. Lampiran PKA --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        8. Lampiran PKA (File Dokumen)
                                    </label>
                                    <input
                                        type="file"
                                        id="pka_lampiran_input"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg"
                                        x-on:change="onFileChange($event)"
                                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs text-neutral-700 file:mr-3 file:rounded file:border-0 file:bg-neutral-900 file:px-2.5 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-neutral-800 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:file:bg-white dark:file:text-neutral-900"
                                    />
                                    <p class="text-[11px] text-neutral-500">Format: PDF, DOC, DOCX, JPG, atau PNG (Maks. 10MB).</p>
                                    <template x-if="existingLampiranName && !lampiranFile">
                                        <p class="text-[11px] text-neutral-500">
                                            Dokumen saat ini: <a :href="existingLampiranUrl" target="_blank" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400" x-text="existingLampiranName"></a> (biarkan kosong jika tidak diganti)
                                        </p>
                                    </template>
                                    <template x-if="errors.lampiran">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.lampiran[0]"></p>
                                    </template>
                                </div>

                                {{-- Dibuat Oleh & Direviu Oleh (50:50) --}}
                                <div class="grid gap-4 sm:col-span-2 sm:grid-cols-2 lg:col-span-3">
                                    <div class="space-y-1.5">
                                        <x-searchable-select
                                            label="Dibuat Oleh"
                                            placeholder="Pilih Pembuat PKA..."
                                            itemsExpr="sprinMemberOptions"
                                            valueModel="form.dibuat_oleh_id"
                                            changeAction=""
                                            disabledExpr="!form.sprin_id"
                                            disabledMessage="Pilih Nomor Sprin terlebih dahulu"
                                        />
                                        <template x-if="errors.dibuat_oleh_id">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.dibuat_oleh_id[0]"></p>
                                        </template>
                                    </div>

                                    <div class="space-y-1.5">
                                        <x-searchable-select
                                            label="Direviu Oleh"
                                            placeholder="Pilih Pereviu PKA..."
                                            itemsExpr="sprinMemberOptions"
                                            valueModel="form.direviu_oleh_id"
                                            changeAction=""
                                            disabledExpr="!form.sprin_id"
                                            disabledMessage="Pilih Nomor Sprin terlebih dahulu"
                                        />
                                        <template x-if="errors.direviu_oleh_id">
                                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.direviu_oleh_id[0]"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 9: Template PKA Selector (Full Width) --}}
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 dark:border-indigo-950/60 dark:bg-indigo-950/20">
                            <x-searchable-select
                                label="9. Template PKA"
                                placeholder="-- Cari dan Pilih Template PKA --"
                                itemsExpr="templateOptions"
                                valueModel="form.template_pka_id"
                                changeAction="onTemplateChange()"
                            />
                        </div>

                        {{-- Section Tabs (Items 10 - 16) --}}
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
                                {{-- Word-like Toolbar --}}
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
                                            title="Rata Kiri Kanan (Justify)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-justify class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Lists: Bullet, Numbered, Indent / Outdent --}}
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

                                {{-- Editor Canvas per Tab --}}
                                @foreach ([
                                    'pendahuluan' => '10. Pendahuluan',
                                    'tujuan' => '11. Tujuan',
                                    'ruang_lingkup' => '12. Ruang Lingkup Audit',
                                    'sasaran' => '13. Sasaran Audit',
                                    'waktu_pelaksanaan' => '14. Waktu Pelaksanaan Auditi',
                                    'periode' => '15. Periode Audit',
                                    'penutup' => '16. Penutup',
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

                        {{-- Status Dokumen (Switch Radio Button seperti Modal KKA) --}}
                        {{-- Status Dokumen (Switch Toggle: Draft di kiri, Final di kanan) --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-neutral-900 dark:text-neutral-100">
                                Status Dokumen <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-xs select-none cursor-pointer transition-colors"
                                    :class="form.status !== 'Final' ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    x-on:click="form.status = 'Draft'"
                                >
                                    Draft
                                </span>

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
                                    class="text-xs select-none cursor-pointer transition-colors"
                                    :class="form.status === 'Final' ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                    x-on:click="form.status = 'Final'"
                                >
                                    Final
                                </span>
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
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan PKA')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Preview Detail PKA --}}
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
                    <div class="space-y-0.5">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
                            Detail Dokumen Program Kerja Audit
                        </h2>
                        <template x-if="previewData">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400" x-text="previewData.nomor_pka"></p>
                        </template>
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
                            <span class="mt-2">Memuat isi dokumen PKA...</span>
                        </div>
                    </template>

                    <template x-if="!loadingPreview && previewData">
                        <div class="space-y-6">
                            {{-- Info Header Card --}}
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 rounded-xl border border-neutral-200 bg-neutral-50/70 p-4 dark:border-neutral-800 dark:bg-neutral-900/40 text-xs">
                                <div>
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Nomor Sprin:</span>
                                    <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="previewData.no_sprin"></span>
                                    <template x-if="previewData.tim_name">
                                        <span class="block text-[11px] text-neutral-500" x-text="'Tim: ' + previewData.tim_name"></span>
                                    </template>
                                </div>
                                <div>
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Jenis Audit:</span>
                                    <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="previewData.jenis_audit"></span>
                                    <span class="block text-[11px] text-neutral-500" x-text="'Bidang: ' + previewData.bidang"></span>
                                </div>
                                <div>
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Tanggal & Anggaran:</span>
                                    <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="previewData.tanggal_formatted"></span>
                                    <span class="block text-[11px] font-medium text-emerald-600 dark:text-emerald-400" x-text="previewData.formatted_anggaran"></span>
                                </div>
                                <div>
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Status Dokumen:</span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold mt-0.5"
                                        :class="previewData.status === 'Final'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                                        x-text="previewData.status || 'Draft'"
                                    ></span>
                                </div>
                                <div>
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Lampiran Dokumen:</span>
                                    <template x-if="previewData.lampiran_url">
                                        <a
                                            :href="previewData.lampiran_url"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                                        >
                                            <x-lucide-file-text class="h-3.5 w-3.5" />
                                            <span x-text="previewData.lampiran_name || 'Unduh File'"></span>
                                        </a>
                                    </template>
                                    <template x-if="!previewData.lampiran_url">
                                        <span class="italic text-neutral-400">Tidak ada lampiran</span>
                                    </template>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-5 border-t border-neutral-200 pt-2 dark:border-neutral-800">
                                    <span class="text-neutral-500 dark:text-neutral-400 block text-[11px]">Sasaran Auditi:</span>
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200" x-text="previewData.sasaran_auditi"></span>
                                </div>
                            </div>

                            {{-- Formatted Sections 10 - 16 --}}
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

        {{-- Modal Konfirmasi Hapus PKA --}}
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
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Dokumen PKA</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Apakah Anda yakin ingin menghapus dokumen PKA <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="deletingItem ? deletingItem.nomor_pka : ''"></span>? Data ini dapat dipulihkan melalui soft delete.
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

        {{-- Modal Duplikasi PKA --}}
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
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Duplikasi Dokumen PKA</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Buat salinan baru dari PKA <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="duplicatingItem ? duplicatingItem.nomor_pka : ''"></span>. Status dokumen baru akan diset ke Draft.
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Nomor PKA Baru <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="duplicateNomorPka"
                        placeholder="Contoh: PKA/002/WIL-I/2026..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
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
                        x-bind:disabled="duplicating || !duplicateNomorPka"
                        x-on:click="submitDuplicate()"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-4 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200"
                    >
                        <template x-if="duplicating">
                            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                        </template>
                        <span x-text="duplicating ? 'Menduplikasi...' : 'Duplikasi Sekarang'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Reviu PKA (Dalnis & Daltu) --}}
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
                {{-- Header Modal Reviu --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                            <x-lucide-clipboard-check class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="reviewType === 'dalnis' ? 'Lembar Reviu PKA — Dalnis' : 'Lembar Reviu PKA — Daltu'"></h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400" x-text="reviewType === 'dalnis' ? 'Pemeriksaan dan persetujuan Pengawas Tim (Dalnis).' : 'Pemeriksaan dan persetujuan Pengendali Mutu (Daltu).'"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeReviewModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <div class="max-h-[80vh] space-y-6 overflow-y-auto px-6 py-5">
                    {{-- Ringkasan Data PKA --}}
                    <div class="rounded-xl border border-neutral-200 bg-neutral-50/60 p-4 dark:border-neutral-800 dark:bg-neutral-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 pb-3 dark:border-neutral-800">
                            <div class="space-y-0.5">
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Nomor PKA & Sprin</span>
                                <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100" x-text="reviewPka?.nomor_pka || '-'"></h3>
                                <p class="text-xs font-medium text-red-600 dark:text-red-400" x-text="'Sprin: ' + (reviewPka?.no_sprin || '-')"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-neutral-500">Status Reviu Saat Ini:</span>
                                <template x-if="reviewPka?.latest_review_status === 'Disetujui'">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Disetujui
                                    </span>
                                </template>
                                <template x-if="reviewPka?.latest_review_status === 'Koreksi'">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                        Koreksi
                                    </span>
                                </template>
                                <template x-if="!reviewPka?.latest_review_status">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        Menunggu Reviu
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Tipe & Tahap</span>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="reviewPka?.sprin_tipe === 'Nasional'
                                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                        x-text="reviewPka?.sprin_tipe || '-'"
                                    ></span>
                                    <template x-if="reviewPka?.tahap && reviewPka?.tahap !== '-'">
                                        <span class="inline-flex items-center rounded-md border border-neutral-200 bg-white px-2 py-0.5 text-[11px] font-medium text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300" x-text="reviewPka.tahap === 'Khusus' ? 'Khusus' : 'Tahap ' + reviewPka.tahap"></span>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Bidang & Jenis Audit</span>
                                <p class="mt-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200" x-text="reviewPka?.bidang || '-'"></p>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="reviewPka?.jenis_audit || '-'"></p>
                            </div>

                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Tanggal & Anggaran</span>
                                <p class="mt-1 text-xs font-medium text-neutral-800 dark:text-neutral-200" x-text="reviewPka?.tanggal_formatted || '-'"></p>
                                <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400" x-text="reviewPka?.formatted_anggaran || '-'"></p>
                            </div>

                            <div class="sm:col-span-2">
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Sasaran Auditi</span>
                                <p class="mt-1 text-xs text-neutral-800 dark:text-neutral-200 break-words" x-text="reviewPka?.sasaran_auditi || '-'"></p>
                            </div>

                            <div>
                                <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Lampiran Dokumen PKA</span>
                                <div class="mt-1">
                                    <template x-if="reviewPka?.lampiran_url">
                                        <a :href="reviewPka.lampiran_url" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-blue-400">
                                            <x-lucide-file-text class="h-3.5 w-3.5" />
                                            <span class="max-w-[14rem] truncate" x-text="reviewPka.lampiran_name || 'Lihat Dokumen PKA'"></span>
                                            <x-lucide-external-link class="h-3 w-3 opacity-60" />
                                        </a>
                                    </template>
                                    <template x-if="!reviewPka?.lampiran_url">
                                        <span class="text-xs text-neutral-400">Tidak ada lampiran</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Log Reviu --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Log Riwayat Reviu</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="'Catatan dan status hasil telaah oleh ' + (reviewPka?.role_label || 'Reviewer') + '.'"></p>
                            </div>
                            <template x-if="reviewPka?.can_review">
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

                        {{-- Form Input Reviu Baru --}}
                        <template x-if="reviewPka?.can_review">
                            <div
                                x-show="reviewFormOpen"
                                x-cloak
                                x-transition
                                class="rounded-xl border border-neutral-300 bg-neutral-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-900/60"
                            >
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
                                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            Catatan Reviu
                                        </label>
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
                                        >
                                            Batal
                                        </button>
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

                        {{-- Tabel DataTable Log Reviu --}}
                        <div class="relative overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-800">
                            <div
                                x-show="loadingReviews"
                                x-cloak
                                class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-[1px] dark:bg-neutral-950/70"
                            >
                                <div class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                                    <x-lucide-loader-circle class="h-3.5 w-3.5 animate-spin" />
                                    <span>Memuat riwayat reviu...</span>
                                </div>
                            </div>

                            <table class="w-full border-collapse text-left text-xs">
                                <thead>
                                    <tr class="border-b border-neutral-200 bg-neutral-50/70 text-[11px] uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400">
                                        <th class="whitespace-nowrap px-3 py-2.5 text-center font-medium w-10">#</th>
                                        <th class="whitespace-nowrap px-3 py-2.5 font-medium w-36">WAKTU</th>
                                        <th class="whitespace-nowrap px-3 py-2.5 font-medium w-40">REVIEWER</th>
                                        <th class="whitespace-nowrap px-3 py-2.5 text-center font-medium w-28">STATUS</th>
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
                                                <template x-if="log.status === 'Disetujui'">
                                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                        Disetujui
                                                    </span>
                                                </template>
                                                <template x-if="log.status === 'Koreksi'">
                                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                                        Koreksi
                                                    </span>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2.5 text-neutral-800 dark:text-neutral-200 break-words" x-text="log.catatan || '-'"></td>
                                        </tr>
                                    </template>

                                    <template x-if="!loadingReviews && reviewLogs.length === 0">
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                                Belum ada riwayat reviu untuk PKA ini.
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
