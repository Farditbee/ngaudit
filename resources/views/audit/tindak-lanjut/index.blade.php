<x-admin-layout :breadcrumbs="['Audit', 'Auditee', 'Tindak Lanjut Auditee']">
    <x-slot:title>Tinjut Auditee</x-slot:title>

    <div x-data="kkaDataTable({{ Js::from($dataTableConfig) }})" class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                Tinjut Auditee
            </h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Daftar Kertas Kerja Audit (KKA) untuk pemberian tanggapan dan tindak lanjut hasil audit oleh Auditee.
            </p>
        </div>

        {{-- DataTable --}}
        <x-data-table
            :columns="$columns"
            search-placeholder="Cari auditee, nomor KKA, auditor, bidang..."
            empty-message="Belum ada data KKA untuk ditindaklanjuti."
            :page-length="10"
        >
            <template x-for="(row, index) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 align-top transition-colors hover:bg-neutral-50/70 dark:border-neutral-800 dark:hover:bg-neutral-900/50"
                    :class="index % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    {{-- 1. # --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3.5 text-center text-xs text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 2. AKSI --}}
                    <td class="border-r border-neutral-200 px-3 py-3.5 text-xs dark:border-neutral-800">
                        <div class="flex flex-col items-start gap-1.5">
                            <a
                                :href="row.tanggapan_url || (`/audit/tindak-lanjut/${row.id}/tabulasi`)"
                                class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-blue-700"
                            >
                                <span>Beri Tanggapan (<span x-text="row.temuan_count ?? 0"></span>)</span>
                            </a>

                            <span class="inline-flex items-center rounded-full bg-sky-500 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                                <span x-text="(row.sudah_ditinjut_count ?? 0) + ' Sudah ditinjut Auditi'"></span>
                            </span>

                            <span class="inline-flex items-center rounded-full bg-amber-500 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                                <span x-text="(row.koreksi_count ?? row.belum_sesuai_count ?? 0) + ' Koreksi'"></span>
                            </span>

                            <span class="inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                                <span x-text="(row.selesai_count ?? 0) + ' Temuan sudah Selesai'"></span>
                            </span>

                            <template x-if="(row.draft_count ?? 0) > 0">
                                <span class="inline-flex items-center rounded border border-neutral-300 bg-white px-2 py-0.5 text-[11px] font-medium text-amber-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-amber-400">
                                    <span x-text="row.draft_count + ' Temuan Masih Draft!'"></span>
                                </span>
                            </template>
                        </div>
                    </td>

                    {{-- 3. AUDITEE --}}
                    <td class="border-r border-neutral-200 px-3.5 py-3.5 text-xs dark:border-neutral-800">
                        <div class="flex flex-col items-start gap-1">
                            {{-- Badge Level (Kecamatan / Dinas) --}}
                            <span
                                class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium leading-normal"
                                :class="row.auditee_level === 'Dinas'
                                    ? 'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-800/60 dark:bg-indigo-950/40 dark:text-indigo-300'
                                    : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'"
                                x-text="row.auditee_parent || row.auditee_name"
                            ></span>

                            {{-- Badge Sub (Satuan Kerja / Seksi / Bidang) --}}
                            <template x-if="row.auditee_parent">
                                <span
                                    class="inline-flex items-center rounded-md border border-purple-200 bg-purple-50/80 px-2 py-0.5 text-[11px] font-medium text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300"
                                    x-text="row.auditee_sub_unit || row.auditee_name"
                                ></span>
                            </template>
                        </div>
                    </td>

                    {{-- 4. KKA / AUDITOR --}}
                    <td class="border-r border-neutral-200 px-3.5 py-3.5 text-xs dark:border-neutral-800">
                        <div class="space-y-1">
                            <div class="font-semibold text-amber-500 dark:text-amber-400" x-text="row.nomor_kka || '-'"></div>
                            <div class="text-[11px] font-medium uppercase text-neutral-700 dark:text-neutral-300" x-text="row.auditor_name || '-'"></div>
                        </div>
                    </td>

                    {{-- 5. BIDANG & TAHAP --}}
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3.5 text-center text-xs dark:border-neutral-800">
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
                                x-text="row.bidang || '-'"
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

                    {{-- 6. TANGGAL --}}
                    <td class="whitespace-nowrap px-3.5 py-3.5 text-xs italic text-neutral-500 dark:text-neutral-400">
                        <span x-text="'(Diinput pada ' + (row.tanggal_diinput || '-') + ')'"></span>
                    </td>
                </tr>
            </template>
        </x-data-table>
    </div>
</x-admin-layout>
