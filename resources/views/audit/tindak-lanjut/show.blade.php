@php
    $pka = $kka->pka;
    $sprin = $pka?->sprin;
    $noKka = $kka->nomor_kka ?: ('KKA '.$kka->tanggal_kka?->format('d/m/Y'));
    $noSprin = $sprin?->no_sprin ?? '-';
    $noPka = $pka?->nomor_pka ?? '-';
    $bidang = $pka?->bidang ?? '';
    $inputClass = 'block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100';
@endphp

<x-admin-layout :breadcrumbs="['Audit', 'Auditee', 'Tindak Lanjut Auditee', $noKka]">
    <x-slot:title>Tanggapan Auditi — {{ $noKka }}</x-slot:title>

    <div
        x-data="tindakLanjutAuditeePage({{ Js::from($pageConfig) }})"
        class="space-y-4"
    >
        {{-- Top Section (Sesuai Screenshot) --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-lg font-bold tracking-tight text-neutral-800 dark:text-neutral-100">
                    Tanggapan Auditi pada KKA Nomor: <span class="text-blue-600 dark:text-blue-400">{{ $noKka }}</span>
                </h1>

                <div class="space-y-0.5 text-xs">
                    <p class="text-neutral-600 dark:text-neutral-400">
                        Dari Sprin: <span class="font-medium italic text-emerald-600 dark:text-emerald-400">{{ $noSprin }}</span>
                    </p>
                    <p class="text-neutral-600 dark:text-neutral-400">
                        Dari PKA: <span class="font-medium italic text-amber-500 dark:text-amber-400">{{ $noPka }}</span>
                    </p>
                </div>

                <div class="pt-1">
                    <a
                        href="{{ route('audit.tindak-lanjut.index') }}"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md bg-slate-600 px-3.5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-slate-700"
                    >
                        <x-lucide-arrow-left class="h-3.5 w-3.5" />
                        <span>Kembali</span>
                    </a>
                </div>
            </div>

            <div class="pt-2">
                <a
                    href="{{ route('audit.tindak-lanjut.print', $kka) }}"
                    target="_blank"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-emerald-600 px-3.5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-emerald-700"
                >
                    <x-lucide-printer class="h-3.5 w-3.5" />
                    <span>Cetak PTL</span>
                </a>
            </div>
        </div>

        {{-- Card 1: Bidang & 3 Tombol Tab (Tabulasi Temuan, Tabulasi Konsultasi, Temuan Positif) --}}
        <div class="rounded-lg border border-neutral-200 bg-white px-5 py-4 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950 space-y-3">
            <div class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                Bidang: <span class="font-bold text-neutral-900 dark:text-white">{{ $bidang }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach ([
                    'tabulasi' => ['label' => 'Tabulasi Temuan', 'jenis' => 'Temuan'],
                    'konsultasi' => ['label' => 'Tabulasi Konsultasi', 'jenis' => 'Konsultasi'],
                    'positif' => ['label' => 'Temuan Positif', 'jenis' => 'Positif'],
                ] as $slug => $tabInfo)
                    @php
                        $isActive = $tabSlug === $slug;
                        $count = $tabCounts[$tabInfo['jenis']] ?? 0;
                    @endphp
                    <a
                        href="{{ route('audit.tindak-lanjut.show', [$kka, $slug]) }}"
                        class="inline-flex h-8 items-center justify-center rounded-md px-3.5 text-xs font-semibold transition-colors {{ $isActive
                            ? 'bg-blue-600 text-white shadow-2xs hover:bg-blue-700'
                            : 'border border-blue-500 bg-white text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:bg-neutral-900 dark:text-blue-400 dark:hover:bg-blue-950/40' }}"
                    >
                        <span>{{ $tabInfo['label'] }} ({{ $count }})</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Card 2: Data Tabulasi & Toggle Tampilkan Temuan Selesai --}}
        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">
                    Data Tabulasi
                </h2>

                <label class="inline-flex cursor-pointer items-center gap-2.5 select-none">
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="showCompleted"
                        x-on:click="showCompleted = !showCompleted"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="showCompleted ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-700'"
                    >
                        <span
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                            :class="showCompleted ? 'translate-x-4' : 'translate-x-0'"
                        ></span>
                    </button>
                    <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Tampilkan Temuan Selesai</span>
                </label>
            </div>

            {{-- Tabel Grid Penuh Sesuai Screenshot (Bisa Scroll Samping saat Layar Kecil) --}}
            <div class="overflow-x-auto rounded border border-neutral-200 dark:border-neutral-800">
                <table class="min-w-[1400px] w-full table-fixed border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50/80 text-[10px] font-bold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/60 dark:text-neutral-400">
                            <th class="w-24 border-r border-neutral-200 px-2 py-2.5 dark:border-neutral-800">STATUS</th>
                            <th class="w-24 border-r border-neutral-200 px-2 py-2.5 dark:border-neutral-800">OPSI</th>
                            <th class="w-10 border-r border-neutral-200 px-1.5 py-2.5 text-center dark:border-neutral-800">NO</th>

                            @if ($jenisTabulasi === 'Temuan')
                                <th class="w-52 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">TEMUAN</th>
                                <th class="w-24 border-r border-neutral-200 px-2 py-2.5 dark:border-neutral-800">KODE TEMUAN</th>
                                <th class="w-44 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">KRITERIA</th>
                                <th class="w-28 border-r border-neutral-200 px-2 py-2.5 dark:border-neutral-800">NILAI</th>
                                <th class="w-48 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">SEBAB</th>
                                <th class="w-44 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">AKIBAT</th>
                                <th class="w-48 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">REKOMENDASI</th>
                            @elseif ($jenisTabulasi === 'Konsultasi')
                                <th class="w-72 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">URAIAN PERMASALAHAN</th>
                                <th class="w-64 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">KRITERIA</th>
                                <th class="w-64 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">REKOMENDASI</th>
                            @else
                                <th class="w-96 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">DESKRIPSI</th>
                            @endif

                            <th class="w-44 border-r border-neutral-200 px-2.5 py-2.5 dark:border-neutral-800">TINDAK LANJUT</th>
                            <th class="w-28 border-r border-neutral-200 px-2 py-2.5 dark:border-neutral-800">KESIMPULAN</th>
                            <th class="w-28 px-2 py-2.5">ALASAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <template x-if="filteredRows.length === 0">
                            <tr>
                                <td colspan="{{ $jenisTabulasi === 'Temuan' ? 13 : ($jenisTabulasi === 'Konsultasi' ? 9 : 7) }}" class="px-4 py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                    Belum ada data tabulasi untuk ditampilkan.
                                </td>
                            </tr>
                        </template>

                        <template x-for="row in filteredRows" :key="row.id">
                            <tr class="align-top transition-colors hover:bg-neutral-50/60 dark:hover:bg-neutral-900/40">
                                {{-- 1. STATUS --}}
                                <td class="border-r border-neutral-200 px-2 py-3 dark:border-neutral-800">
                                    <span
                                        class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold leading-tight text-white"
                                        :class="{
                                            'bg-red-500': row.status_tinjut === 'Belum Ditinjut' || !row.status_tinjut,
                                            'bg-sky-500': row.status_tinjut === 'Dalam Proses',
                                            'bg-amber-500': row.status_tinjut === 'Belum Sesuai',
                                            'bg-emerald-600': row.status_tinjut === 'Selesai',
                                        }"
                                        x-text="row.status_tinjut || 'Belum Ditinjut'"
                                    ></span>
                                </td>

                                {{-- 2. OPSI (Tanggapi & Lampiran) --}}
                                <td class="border-r border-neutral-200 px-2 py-3 dark:border-neutral-800">
                                    <div class="flex flex-col items-stretch gap-1.5">
                                        <button
                                            type="button"
                                            x-on:click="openTanggapiModal(row, 'informasi')"
                                            class="inline-flex items-center justify-center gap-1 rounded bg-blue-600 px-2 py-1 text-[10px] font-semibold text-white shadow-2xs transition-colors hover:bg-blue-700"
                                        >
                                            <x-lucide-message-circle class="h-3 w-3 shrink-0" />
                                            <span>Tanggapi</span>
                                        </button>

                                        <button
                                            type="button"
                                            x-on:click="openTanggapiModal(row, 'lampiran')"
                                            class="inline-flex items-center justify-center gap-1 rounded bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white shadow-2xs transition-colors hover:bg-emerald-700"
                                        >
                                            <x-lucide-paperclip class="h-3 w-3 shrink-0" />
                                            <span>Lampiran</span>
                                        </button>
                                    </div>
                                </td>

                                {{-- 3. NO --}}
                                <td class="break-words border-r border-neutral-200 px-1.5 py-3 text-center font-medium text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.nomor"></td>

                                @if ($jenisTabulasi === 'Temuan')
                                    {{-- 4. TEMUAN --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                                    </td>

                                    {{-- 5. KODE TEMUAN --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <template x-if="row.kode_temuan_kode || row.sub_kode_temuan_kode">
                                            <div class="space-y-0.5">
                                                <div class="font-semibold" x-text="row.kode_temuan_kode || '-'"></div>
                                                <template x-if="row.sub_kode_temuan_kode">
                                                    <div class="text-[10px] text-neutral-600 dark:text-neutral-400">
                                                        Sub kode : <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="row.sub_kode_temuan_kode"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!row.kode_temuan_kode && !row.sub_kode_temuan_kode">
                                            <span>-</span>
                                        </template>
                                    </td>

                                    {{-- 6. KRITERIA --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_kriteria || '-'"></div>
                                    </td>

                                    {{-- 7. NILAI --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-3 font-medium leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.nilai_temuan_formatted"></td>

                                    {{-- 8. SEBAB --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_sebab || '-'"></div>
                                    </td>

                                    {{-- 9. AKIBAT --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_akibat || '-'"></div>
                                    </td>

                                    {{-- 10. REKOMENDASI --}}
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_rekomendasi || '-'"></div>
                                    </td>
                                @elseif ($jenisTabulasi === 'Konsultasi')
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                                    </td>
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_kriteria || '-'"></div>
                                    </td>
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_rekomendasi || '-'"></div>
                                    </td>
                                @else
                                    <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                        <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                                    </td>
                                @endif

                                {{-- 11. TINDAK LANJUT --}}
                                <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-3 leading-relaxed text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                                    <div class="prose prose-xs max-w-none break-words text-[11px] leading-relaxed dark:prose-invert" x-html="row.tindak_lanjut || ''"></div>
                                    <template x-if="row.nilai_pengembalian_formatted">
                                        <div class="mt-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400" x-text="'Pengembalian: ' + row.nilai_pengembalian_formatted"></div>
                                    </template>
                                    <template x-if="row.lampiran_tinjut_url">
                                        <a
                                            :href="row.lampiran_tinjut_url"
                                            target="_blank"
                                            class="mt-1.5 inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 hover:underline dark:bg-emerald-950/50 dark:text-emerald-300"
                                        >
                                            <x-lucide-paperclip class="h-2.5 w-2.5" />
                                            <span class="max-w-[8rem] truncate" x-text="row.lampiran_tinjut_name || 'Lihat Lampiran'"></span>
                                        </a>
                                    </template>
                                </td>

                                {{-- 12. KESIMPULAN --}}
                                <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.kesimpulan_tinjut || ''"></td>

                                {{-- 13. ALASAN --}}
                                <td class="whitespace-normal break-words px-2 py-3 text-neutral-800 dark:text-neutral-200" x-text="row.alasan_tinjut || ''"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL BESAR TANGGAPI (Konversasi Tanggapan + Sidebar Informasi, Uji Nilai, Lampiran, Status) --}}
        <div
            x-show="tanggapiModalOpen"
            x-cloak
            x-on:keydown.escape.window="closeTanggapiModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-3 sm:p-5"
        >
            <div x-show="tanggapiModalOpen" x-transition.opacity x-on:click="closeTanggapiModal()" class="fixed inset-0 bg-black/55 backdrop-blur-[1px]"></div>

            <div
                x-show="tanggapiModalOpen"
                x-transition
                class="relative z-10 my-4 flex max-h-[92vh] w-full max-w-[94vw] flex-col overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100 shadow-2xl xl:max-w-7xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                {{-- Header Modal --}}
                <div class="flex shrink-0 items-center justify-between border-b border-neutral-200 bg-white px-6 py-3.5 dark:border-neutral-800 dark:bg-neutral-950">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100">
                            Tanggapan & Tindak Lanjut — Temuan No. <span x-text="activeRow?.nomor"></span>
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            KKA Nomor: <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $noKka }}</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        x-on:click="closeTanggapiModal()"
                        class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <x-lucide-x class="h-5 w-5" />
                    </button>
                </div>

                {{-- Body Modal Scrollable --}}
                <div class="flex-1 space-y-5 overflow-y-auto p-4 sm:p-6">
                    {{-- 1. KONVERSASI TANGGAPAN (SS ke-1) --}}
                    <div class="rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                        <div class="border-b border-neutral-200 px-5 py-3.5 dark:border-neutral-800">
                            <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">
                                Konversasi Tanggapan
                            </h4>
                        </div>

                        <div class="space-y-4 p-5">
                            {{-- Daftar Pesan Chat --}}
                            <div class="max-h-64 space-y-4 overflow-y-auto pr-1">
                                <template x-if="!activeRow?.messages || activeRow.messages.length === 0">
                                    <div class="py-4 text-center text-xs text-neutral-400 dark:text-neutral-500">
                                        Belum ada percakapan tanggapan. Mulai obrolan di bawah ini.
                                    </div>
                                </template>

                                <template x-for="msg in (activeRow?.messages || [])" :key="msg.id">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 text-white">
                                                <x-lucide-user class="h-4 w-4" />
                                            </div>
                                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="msg.time_ago"></span>
                                        </div>

                                        <div class="rounded-lg bg-neutral-100/90 px-4 py-3 text-xs dark:bg-neutral-900">
                                            <div class="font-bold text-neutral-800 dark:text-neutral-100" x-text="msg.sender_name"></div>
                                            <template x-if="msg.pesan">
                                                <p class="mt-0.5 whitespace-pre-line text-neutral-700 dark:text-neutral-300" x-text="msg.pesan"></p>
                                            </template>
                                            <template x-if="msg.file_url">
                                                <div class="mt-2">
                                                    <a
                                                        :href="msg.file_url"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 rounded border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-300"
                                                    >
                                                        <x-lucide-paperclip class="h-3 w-3" />
                                                        <span x-text="msg.file_name || 'Unduh Lampiran'"></span>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Form Input Chat (Choose File + Pesan + Tombol Kirim) --}}
                            <form x-on:submit.prevent="sendChatMessage()" class="border-t border-neutral-200 pt-4 dark:border-neutral-800">
                                <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                                    <div class="sm:w-72">
                                        <input
                                            type="file"
                                            x-ref="chatFileInput"
                                            x-on:change="onChatFileSelect($event)"
                                            class="block h-9 w-full rounded-md border border-neutral-200 bg-white text-xs text-neutral-700 file:mr-3 file:h-full file:border-0 file:border-r file:border-neutral-200 file:bg-neutral-100 file:px-3 file:text-xs file:font-medium file:text-neutral-700 hover:file:bg-neutral-200 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:file:border-neutral-700 dark:file:bg-neutral-800 dark:file:text-neutral-200"
                                        />
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="text"
                                            x-model="chatForm.pesan"
                                            placeholder="Ketikkan pesan Anda (opsional jika melampirkan file)"
                                            class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-blue-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                        />
                                    </div>

                                    <button
                                        type="submit"
                                        x-bind:disabled="sendingChat"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-blue-600 px-6 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        <x-lucide-loader-circle x-show="sendingChat" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                        <span>Kirim</span>
                                    </button>
                                </div>
                                <template x-if="chatError">
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400" x-text="chatError"></p>
                                </template>
                            </form>
                        </div>
                    </div>

                    {{-- 2. CONTAINER SIDEBAR DI KIRI (Informasi, Uji Nilai, Lampiran, Status) & KONTEN DI KANAN --}}
                    <div class="grid items-start gap-5 lg:grid-cols-12">
                        {{-- Sidebar Kiri (Sticky / Freeze saat di-scroll ke bawah) --}}
                        <div class="sticky top-0 z-10 space-y-3 lg:col-span-3">
                            <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    <button
                                        type="button"
                                        x-on:click="switchSidebarTab('informasi')"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-xs font-semibold transition-colors"
                                        :class="sidebarTab === 'informasi'
                                            ? 'bg-blue-600 text-white'
                                            : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-900'"
                                    >
                                        <span class="inline-flex items-center gap-2">
                                            <x-lucide-info class="h-4 w-4 shrink-0" />
                                            <span>Tinjut Auditee</span>
                                        </span>
                                        <x-lucide-chevron-right class="h-3.5 w-3.5 opacity-75" />
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="switchSidebarTab('lampiran')"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-xs font-semibold transition-colors"
                                        :class="sidebarTab === 'lampiran'
                                            ? 'bg-blue-600 text-white'
                                            : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-900'"
                                    >
                                        <span class="inline-flex items-center gap-2">
                                            <x-lucide-paperclip class="h-4 w-4 shrink-0" />
                                            <span>Lampiran</span>
                                        </span>
                                        <span
                                            class="inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                                            :class="sidebarTab === 'lampiran' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'"
                                            x-text="(activeRow?.lampirans || []).length"
                                        ></span>
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="switchSidebarTab('uji_nilai')"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-xs font-semibold transition-colors"
                                        :class="sidebarTab === 'uji_nilai'
                                            ? 'bg-blue-600 text-white'
                                            : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-900'"
                                    >
                                        <span class="inline-flex items-center gap-2">
                                            <x-lucide-clipboard-check class="h-4 w-4 shrink-0" />
                                            <span>Uji Nilai</span>
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold text-white"
                                            :class="{
                                                'bg-red-500': (activeRow?.status_tinjut || 'Belum Ditinjut') === 'Belum Ditinjut',
                                                'bg-sky-500': activeRow?.status_tinjut === 'Dalam Proses',
                                                'bg-amber-500': activeRow?.status_tinjut === 'Belum Sesuai',
                                                'bg-emerald-600': activeRow?.status_tinjut === 'Selesai',
                                            }"
                                            x-text="activeRow?.status_tinjut || 'Belum Ditinjut'"
                                        ></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Konten Kanan per Tab --}}
                        <div class="lg:col-span-9">
                            {{-- TAB 1: INFORMASI (SS ke-2) --}}
                            <div x-show="sidebarTab === 'informasi'" class="rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                <div class="border-b border-neutral-200 px-5 py-3.5 dark:border-neutral-800">
                                    <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Informasi</h4>
                                </div>

                                <div class="p-5 space-y-4">
                                    {{-- Metadata Auditi, KKA No, Oleh Auditor --}}
                                    <div class="space-y-1.5 text-xs">
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Auditi:</span>
                                            <span class="ml-1 inline-flex items-center rounded bg-amber-500 px-2 py-0.5 text-[11px] font-bold uppercase text-white">
                                                {{ $auditeeName }}
                                            </span>
                                        </div>
                                        <div class="font-bold text-neutral-800 dark:text-neutral-100">
                                            KKA No: {{ $noKka }}
                                        </div>
                                        <div class="font-bold uppercase text-neutral-800 dark:text-neutral-100">
                                            Oleh Auditor: {{ $auditorName }}
                                        </div>
                                    </div>

                                    {{-- Tabel Rincian Informasi & Form Tindak Lanjut Auditee --}}
                                    <form x-on:submit.prevent="saveTanggapan()">
                                        <div class="overflow-hidden rounded border border-neutral-200 dark:border-neutral-800">
                                            <table class="w-full border-collapse text-left text-xs">
                                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                                    <tr>
                                                        <td class="w-36 border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Bidang</td>
                                                        <td class="w-4 border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                        <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">{{ $bidang ?: '-' }}</td>
                                                    </tr>

                                                    <tr>
                                                        <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">
                                                            {{ $jenisTabulasi === 'Konsultasi' ? 'Uraian Permasalahan' : ($jenisTabulasi === 'Positif' ? 'Deskripsi' : 'Temuan') }}
                                                        </td>
                                                        <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                        <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">
                                                            <div class="prose prose-xs max-w-none break-words dark:prose-invert" x-html="activeRow?.uraian_temuan || '-'"></div>
                                                        </td>
                                                    </tr>

                                                    @if ($jenisTabulasi === 'Temuan')
                                                        <tr>
                                                            <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Kode Temuan</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5 align-top font-medium text-neutral-800 dark:text-neutral-200">
                                                                <span x-text="activeRow?.kode_temuan_kode || '-'"></span>
                                                                <template x-if="activeRow?.sub_kode_temuan_kode">
                                                                    <span class="ml-1 text-neutral-500" x-text="'(Sub kode: ' + activeRow.sub_kode_temuan_kode + ')'"></span>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($jenisTabulasi !== 'Positif')
                                                        <tr>
                                                            <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Kriteria</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">
                                                                <div class="prose prose-xs max-w-none break-words dark:prose-invert" x-html="activeRow?.uraian_kriteria || '-'"></div>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($jenisTabulasi === 'Temuan')
                                                        <tr>
                                                            <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Sebab</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">
                                                                <div class="prose prose-xs max-w-none break-words dark:prose-invert" x-html="activeRow?.uraian_sebab || '-'"></div>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Akibat</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">
                                                                <div class="prose prose-xs max-w-none break-words dark:prose-invert" x-html="activeRow?.uraian_akibat || '-'"></div>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($jenisTabulasi !== 'Positif')
                                                        <tr>
                                                            <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-2.5 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">Rekomendasi</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-2.5 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5 align-top text-neutral-800 dark:text-neutral-200">
                                                                <div class="prose prose-xs max-w-none break-words dark:prose-invert" x-html="activeRow?.uraian_rekomendasi || '-'"></div>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    {{-- Baris Tindak Lanjut Auditee --}}
                                                    <tr>
                                                        <td class="border-r border-neutral-200 bg-neutral-50/50 px-3 py-3 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300">
                                                            Tindak Lanjut Auditee
                                                        </td>
                                                        <td class="border-r border-neutral-200 px-1.5 py-3 align-top text-center dark:border-neutral-800">:</td>
                                                        <td class="p-3 align-top space-y-4">
                                                            <div class="grid gap-3 sm:grid-cols-2">
                                                                <div>
                                                                    <x-searchable-select
                                                                        label="Kode Tindak Lanjut *"
                                                                        placeholder="Silahkan Pilih"
                                                                        itemsExpr="kodeTinjutOptions"
                                                                        valueModel="form.kode_tinjut_id"
                                                                        changeAction="onKodeTinjutChange()"
                                                                        :wrap="true"
                                                                    />
                                                                </div>
                                                                <div>
                                                                    <x-searchable-select
                                                                        label="Sub Kode Tindak Lanjut"
                                                                        placeholder="Silahkan Pilih Sub Kode"
                                                                        itemsExpr="subKodeTinjutOptions"
                                                                        valueModel="form.sub_kode_tinjut_id"
                                                                        changeAction="onSubKodeTinjutChange()"
                                                                        disabledExpr="!form.kode_tinjut_id"
                                                                        disabledMessage="Pilih Kode Tindak Lanjut terlebih dahulu"
                                                                        :wrap="true"
                                                                    />
                                                                </div>
                                                            </div>

                                                            <div class="space-y-1.5">
                                                                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
                                                                    Isi Tindak Lanjut:
                                                                </label>
                                                                <div class="rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                                                    <x-tiptap-toolbar />
                                                                    <div
                                                                        x-ref="editor_tindak_lanjut"
                                                                        data-tinjut-editor="tindak_lanjut"
                                                                        x-on:click="focusActiveEditor($event)"
                                                                        class="tiptap max-h-[260px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                                                    ></div>
                                                                </div>
                                                                <template x-if="errors.tindak_lanjut">
                                                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tindak_lanjut[0]"></p>
                                                                </template>
                                                            </div>

                                                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                                                                <label class="mb-1.5 block text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                                                                    Nilai Pengembalian (IDR)
                                                                </label>
                                                                <div class="flex rounded-md shadow-xs">
                                                                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-neutral-200 bg-neutral-100 px-3 text-xs font-semibold text-neutral-700 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                                                                        IDR
                                                                    </span>
                                                                    <input
                                                                        type="text"
                                                                        inputmode="numeric"
                                                                        :value="formatRupiahInput(form.nilai_pengembalian)"
                                                                        x-on:input="onNilaiPengembalianInput($event)"
                                                                        placeholder="Contoh: 125.000.000"
                                                                        class="block h-9 w-full rounded-r-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                                                    />
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <button
                                                                    type="submit"
                                                                    x-bind:disabled="saving"
                                                                    class="inline-flex h-8 items-center gap-1.5 rounded bg-blue-600 px-4 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                                                                >
                                                                    <x-lucide-loader-circle x-show="saving" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                                                    <span>Simpan</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- TAB 2: UJI NILAI & STATUS (SS ke-3) --}}
                            <div x-show="sidebarTab === 'uji_nilai'" class="rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                <div class="border-b border-neutral-200 px-5 py-3.5 dark:border-neutral-800">
                                    <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Uji Nilai Auditor</h4>
                                </div>

                                <div class="p-5">
                                    {{-- Jika user bukan Auditor ATAU Auditee belum mengisi tindak lanjut (SS ke-3) --}}
                                    <template x-if="!canEditUjiNilai">
                                        <div class="space-y-3">
                                            <div class="overflow-hidden rounded border border-neutral-200 dark:border-neutral-800">
                                                <table class="w-full border-collapse text-left text-xs">
                                                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                                        <tr>
                                                            <td class="w-36 border-r border-neutral-200 px-3 py-3 font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">Kesimpulan</td>
                                                            <td class="w-4 border-r border-neutral-200 px-1.5 py-3 text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-3 italic text-neutral-600 dark:text-neutral-400">
                                                                <span x-text="!canUjiNilaiRole ? 'Untuk mengisi kesimpulan hanya akun dengan role Auditor' : 'Untuk mengisi kesimpulan, Auditee harus mengisi tindak lanjut terlebih dahulu'"></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-r border-neutral-200 px-3 py-3 font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">Alasan</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-3 text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-3 italic text-neutral-600 dark:text-neutral-400">
                                                                <span x-text="!canUjiNilaiRole ? 'Untuk mengisi alasan hanya akun dengan role Auditor' : 'Untuk mengisi alasan, Auditee harus mengisi tindak lanjut terlebih dahulu'"></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-r border-neutral-200 px-3 py-3 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">Status</td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-3 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-3 space-y-1.5">
                                                                <div class="italic text-neutral-600 dark:text-neutral-400" x-text="!canUjiNilaiRole ? 'Yang bisa uji nilai hanya akun auditor' : 'Menunggu tindak lanjut dari Auditee'"></div>
                                                                <div>
                                                                    <span
                                                                        class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold text-white"
                                                                        :class="{
                                                                            'bg-red-500': (activeRow?.status_tinjut || 'Belum Ditinjut') === 'Belum Ditinjut',
                                                                            'bg-sky-500': activeRow?.status_tinjut === 'Dalam Proses',
                                                                            'bg-amber-500': activeRow?.status_tinjut === 'Belum Sesuai',
                                                                            'bg-emerald-600': activeRow?.status_tinjut === 'Selesai',
                                                                        }"
                                                                        x-text="activeRow?.status_tinjut || 'Belum Ditinjut'"
                                                                    ></span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Jika user adalah Auditor DAN Auditee sudah mengisi tindak lanjut --}}
                                    <template x-if="canEditUjiNilai">
                                        <form x-on:submit.prevent="saveUjiNilai()" class="space-y-4">
                                            <div class="overflow-hidden rounded border border-neutral-200 dark:border-neutral-800">
                                                <table class="w-full border-collapse text-left text-xs">
                                                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                                        <tr>
                                                            <td class="w-36 border-r border-neutral-200 px-3 py-3 font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">
                                                                Kesimpulan <span class="text-red-500">*</span>
                                                            </td>
                                                            <td class="w-4 border-r border-neutral-200 px-1.5 py-3 text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5">
                                                                <select x-model="ujiForm.kesimpulan_tinjut" class="{{ $inputClass }}">
                                                                    <option value="">-- Pilih Kesimpulan --</option>
                                                                    <template x-for="ks in kesimpulanTinjuts" :key="ks">
                                                                        <option :value="ks" x-text="ks"></option>
                                                                    </template>
                                                                </select>
                                                                <template x-if="ujiErrors.kesimpulan_tinjut">
                                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="ujiErrors.kesimpulan_tinjut[0]"></p>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-r border-neutral-200 px-3 py-3 align-top font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">
                                                                Alasan
                                                            </td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-3 align-top text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5">
                                                                <textarea
                                                                    rows="3"
                                                                    x-model="ujiForm.alasan_tinjut"
                                                                    placeholder="Tuliskan alasan atau evaluasi atas tindak lanjut auditee..."
                                                                    class="block w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                                                ></textarea>
                                                                <template x-if="ujiErrors.alasan_tinjut">
                                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="ujiErrors.alasan_tinjut[0]"></p>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-r border-neutral-200 px-3 py-3 font-medium text-neutral-700 dark:border-neutral-800 dark:text-neutral-300">
                                                                Status <span class="text-red-500">*</span>
                                                            </td>
                                                            <td class="border-r border-neutral-200 px-1.5 py-3 text-center dark:border-neutral-800">:</td>
                                                            <td class="px-3 py-2.5">
                                                                <select x-model="ujiForm.status_tinjut" class="{{ $inputClass }}">
                                                                    <template x-for="st in statusTinjuts" :key="st">
                                                                        <option :value="st" x-text="st"></option>
                                                                    </template>
                                                                </select>
                                                                <template x-if="ujiErrors.status_tinjut">
                                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="ujiErrors.status_tinjut[0]"></p>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="flex justify-end">
                                                <button
                                                    type="submit"
                                                    x-bind:disabled="savingUji"
                                                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-blue-600 px-5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                                                >
                                                    <x-lucide-loader-circle x-show="savingUji" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                                    <span>Simpan Uji Nilai</span>
                                                </button>
                                            </div>
                                        </form>
                                    </template>
                                </div>
                            </div>

                            {{-- TAB 3: LAMPIRAN (SS ke-4) --}}
                            <div x-show="sidebarTab === 'lampiran'" class="rounded-lg border border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
                                <div class="border-b border-neutral-200 px-5 py-3.5 dark:border-neutral-800">
                                    <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Lampiran</h4>
                                    <div class="mt-2.5 inline-block rounded-md border border-sky-200 bg-sky-50/80 px-4 py-2.5 text-xs text-neutral-700 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-neutral-200">
                                        <strong class="font-bold">Info:</strong> Untuk file berukuran besar direkomendasikan menyisipkan link google drive.
                                    </div>
                                </div>

                                <div class="p-5 space-y-5">
                                    <form x-on:submit.prevent="uploadLampiran()" class="space-y-4">
                                        {{-- Pilihan Jenis Unggahan Lampiran * --}}
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
                                                Pilihan Jenis Unggahan Lampiran <span class="text-red-500">*</span>
                                            </label>
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    x-on:click="lampiranForm.jenis_unggahan = 'file'"
                                                    class="inline-flex h-9 items-center gap-2 rounded-md border px-3.5 text-xs font-medium transition-colors"
                                                    :class="lampiranForm.jenis_unggahan === 'file'
                                                        ? 'border-blue-600 bg-white text-blue-600 ring-1 ring-blue-600 dark:bg-neutral-900 dark:text-blue-400'
                                                        : 'border-neutral-300 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400'"
                                                >
                                                    <x-lucide-file-up class="h-3.5 w-3.5" />
                                                    <span>Upload File (PDF, DOC, Gambar, dll)</span>
                                                </button>

                                                <button
                                                    type="button"
                                                    x-on:click="lampiranForm.jenis_unggahan = 'link'"
                                                    class="inline-flex h-9 items-center gap-2 rounded-md border px-3.5 text-xs font-medium transition-colors"
                                                    :class="lampiranForm.jenis_unggahan === 'link'
                                                        ? 'border-blue-600 bg-white text-blue-600 ring-1 ring-blue-600 dark:bg-neutral-900 dark:text-blue-400'
                                                        : 'border-neutral-300 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400'"
                                                >
                                                    <x-lucide-link class="h-3.5 w-3.5" />
                                                    <span>Tautan / Link URL (Google Drive, dll)</span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Input File atau Link URL --}}
                                        <div x-show="lampiranForm.jenis_unggahan === 'file'" class="space-y-1.5">
                                            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
                                                Upload Lampiran Pendukung (PDF, DOC, Gambar, dll) <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="file"
                                                x-ref="lampiranInput"
                                                x-on:change="onFileSelect($event)"
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.bmp"
                                                class="block h-9 w-full rounded-md border border-neutral-200 bg-white text-xs text-neutral-700 file:mr-3 file:h-full file:border-0 file:border-r file:border-neutral-200 file:bg-neutral-100 file:px-3.5 file:text-xs file:font-medium file:text-neutral-700 hover:file:bg-neutral-200 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:file:border-neutral-700 dark:file:bg-neutral-800 dark:file:text-neutral-200"
                                            />
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                                Format file yang diperbolehkan: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, BMP (Maksimal 10MB)
                                            </p>
                                            <template x-if="lampiranErrors.lampiran">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="lampiranErrors.lampiran[0]"></p>
                                            </template>
                                        </div>

                                        <div x-show="lampiranForm.jenis_unggahan === 'link'" x-cloak class="space-y-1.5">
                                            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
                                                Tautan / Link URL Lampiran (Google Drive, dll) <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="url"
                                                x-model="lampiranForm.link_url"
                                                placeholder="https://drive.google.com/..."
                                                class="{{ $inputClass }}"
                                            />
                                            <template x-if="lampiranErrors.link_url">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="lampiranErrors.link_url[0]"></p>
                                            </template>
                                        </div>

                                        {{-- Keterangan Lampiran * --}}
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
                                                Keterangan Lampiran <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                x-model="lampiranForm.keterangan"
                                                placeholder="Tuliskan keterangan isi lampiran..."
                                                class="{{ $inputClass }}"
                                            />
                                            <template x-if="lampiranErrors.keterangan">
                                                <p class="text-xs text-red-600 dark:text-red-400" x-text="lampiranErrors.keterangan[0]"></p>
                                            </template>
                                        </div>

                                        <div class="flex justify-end">
                                            <button
                                                type="submit"
                                                x-bind:disabled="uploadingLampiran"
                                                class="inline-flex h-9 items-center gap-2 rounded-md bg-blue-600 px-5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                <x-lucide-loader-circle x-show="uploadingLampiran" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                                <span>Kirim Lampiran</span>
                                            </button>
                                        </div>
                                    </form>

                                    {{-- Daftar Lampiran yang Sudah Dikirim --}}
                                    <template x-if="(activeRow?.lampirans || []).length > 0">
                                        <div class="border-t border-neutral-200 pt-4 dark:border-neutral-800 space-y-2.5">
                                            <h5 class="text-xs font-bold text-neutral-700 dark:text-neutral-300">Daftar Lampiran Terkirim</h5>
                                            <div class="divide-y divide-neutral-200 rounded-lg border border-neutral-200 dark:divide-neutral-800 dark:border-neutral-800">
                                                <template x-for="item in activeRow.lampirans" :key="item.id">
                                                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 text-xs">
                                                        <div class="space-y-0.5">
                                                            <div class="font-semibold text-neutral-800 dark:text-neutral-100" x-text="item.keterangan"></div>
                                                            <div class="text-[11px] text-neutral-500">
                                                                <span x-text="item.file_name"></span>
                                                                <span> • </span>
                                                                <span x-text="item.created_at"></span>
                                                            </div>
                                                        </div>
                                                        <template x-if="item.url">
                                                            <a
                                                                :href="item.url"
                                                                target="_blank"
                                                                class="inline-flex items-center gap-1 rounded bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-300"
                                                            >
                                                                <x-lucide-external-link class="h-3 w-3" />
                                                                <span>Buka Lampiran</span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
