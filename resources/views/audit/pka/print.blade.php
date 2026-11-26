@php
    $pengawasTimName = strtoupper((string) ($sprin?->pengawasTim?->name ?? '-'));
    $sasaranAuditiName = strtoupper((string) ($pka->sasaran_auditi ?? '-'));

    $dibuatUser = $pka->dibuatOleh ?? $pka->creator;
    $dibuatOlehLabel = $dibuatUser?->name ?? '-';

    $direviuUser = $pka->direviuOleh ?? $sprin?->pengawasTim;
    $direviuOlehLabel = $direviuUser?->name ?? '-';

    $tahunBuku = $pka->tanggal?->format('Y') ?? now()->format('Y');
    $tanggalPkaFormatted = $pka->tanggal
        ? $pka->tanggal->locale('id')->translatedFormat('d F Y')
        : '-';

    $sectionsBeforeTeam = [
        ['label' => 'I. PENDAHULUAN', 'content' => $pka->pendahuluan],
        ['label' => 'II. TUJUAN', 'content' => $pka->tujuan],
        ['label' => 'III. RUANG LINGKUP AUDIT', 'content' => $pka->ruang_lingkup],
        ['label' => 'IV. SASARAN AUDIT', 'content' => $pka->sasaran],
        ['label' => 'V. WAKTU PELAKSANAAN AUDITI', 'content' => $pka->waktu_pelaksanaan],
        ['label' => 'VI. PERIODE AUDIT', 'content' => $pka->periode],
    ];

    $daltuCount = $sprin?->pengendaliMutu ? 1 : 0;
    $dalnisCount = $sprin?->pengawasTim ? 1 : 0;
    $katimCount = ($sprin?->ketuaTim ? 1 : 0) + ($sprin?->ketuaTim2 ? 1 : 0);
    $auditorCount = $sprin?->tim?->auditors ? $sprin->tim->auditors->count() : 0;
    $totalTimCount = $daltuCount + $dalnisCount + $katimCount + $auditorCount;

    $langkahKerjas = $pka->langkahKerjas ?? collect();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak PKA - {{ $pka->nomor_pka }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 18mm 20mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            color: #111827;
            background: #f3f4f6;
            line-height: 1.6;
            font-size: 12pt;
        }

        .report-sheet {
            max-width: 210mm;
            margin: 24px auto;
            background: #ffffff;
            padding: 20mm 18mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }

        .report-section-body {
            text-align: justify;
        }

        .report-section-body p,
        .report-section-body li {
            text-align: justify;
            margin-bottom: 0.5rem;
        }

        .report-section-body ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin: 0.4rem 0 0.75rem 0;
        }

        .report-section-body ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin: 0.4rem 0 0.75rem 0;
        }

        .report-section-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.75rem 0;
            font-size: 11pt;
        }

        .report-section-body table th,
        .report-section-body table td {
            border: 1px solid #111827;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
        }

        .report-section-body table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            margin-top: 10px;
        }

        .sig-table th,
        .sig-table td {
            border: 1px solid #111827;
            padding: 7px 8px;
            vertical-align: middle;
        }

        .sig-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            .no-print {
                display: none !important;
            }

            .report-sheet {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .page-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    {{-- Floating Action Bar (Hidden on Print) --}}
    <div class="no-print sticky top-0 z-50 flex items-center justify-between border-b border-neutral-200 bg-white/95 px-6 py-3 shadow-xs backdrop-blur">
        <div class="flex items-center gap-3 font-sans">
            <span class="text-sm font-bold text-neutral-900">Pratinjau Cetak PDF PKA</span>
            <span class="rounded-md bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-700">{{ $pka->nomor_pka }}</span>
        </div>
        <div class="flex items-center gap-2 font-sans">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-xs font-semibold text-white shadow-xs hover:bg-neutral-800"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/>
                    <rect x="6" y="14" width="12" height="8" rx="1"/>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button
                type="button"
                onclick="window.close(); if (!window.closed) { window.history.back(); }"
                class="inline-flex h-9 items-center rounded-md border border-neutral-300 bg-white px-3.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
            >
                Tutup
            </button>
        </div>
    </div>

    {{-- Lembar Dokumen PKA --}}
    <div class="report-sheet">
        <div class="report-content space-y-5">
            {{-- Judul Laporan --}}
            <div class="text-center">
                <h1 class="text-base font-bold uppercase leading-snug tracking-wide">
                    PKA {{ strtoupper($pka->bidang) }} OLEH {{ $pengawasTimName }}
                </h1>
                <h2 class="text-base font-bold uppercase leading-snug tracking-wide">
                    TERHADAP {{ $sasaranAuditiName }}
                </h2>

                {{-- Garis Pembatas --}}
                <div class="my-2.5 border-b-2 border-black"></div>

                <div class="text-center text-sm font-bold">
                    Nomor : {{ $pka->nomor_pka }}
                </div>
            </div>

            {{-- Metadata: Dibuat oleh, Direviu oleh, Tahun buku --}}
            <div class="pt-2">
                <table class="meta-table text-sm">
                    <tbody>
                        <tr>
                            <td class="w-36 font-semibold">Dibuat oleh</td>
                            <td class="w-4">:</td>
                            <td>{{ $dibuatOlehLabel }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Direviu oleh</td>
                            <td>:</td>
                            <td>{{ $direviuOlehLabel }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Tahun buku</td>
                            <td>:</td>
                            <td>{{ $tahunBuku }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Isi PKA: I. Pendahuluan s/d VI. Periode Audit (Rata Kiri-Kanan) --}}
            <div class="space-y-4 pt-2">
                @foreach ($sectionsBeforeTeam as $section)
                    <div class="space-y-1.5">
                        <h3 class="text-sm font-bold uppercase">{{ $section['label'] }}</h3>
                        <div class="report-section-body text-sm">
                            @if (! empty(trim(strip_tags((string) $section['content']))) || str_contains((string) $section['content'], '<table') || str_contains((string) $section['content'], '<img'))
                                {!! $section['content'] !!}
                            @else
                                <p class="italic text-neutral-500">-</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- VII. SUSUNAN TIM AUDIT --}}
                <div class="space-y-1.5">
                    <h3 class="text-sm font-bold uppercase">VII. SUSUNAN TIM AUDIT</h3>
                    <div class="pl-5 text-sm space-y-1">
                        <p>a. {{ $daltuCount }} Orang Koordinator dan Pengendali</p>
                        <p>b. {{ $dalnisCount }} Orang Pengawas Tim</p>
                        <p>c. {{ $katimCount }} Orang Ketua Tim</p>
                        <p>d. {{ $auditorCount }} Orang Anggota (Auditor)</p>
                        <p class="pt-1 font-semibold">Jumlah : {{ $totalTimCount }} Orang</p>
                    </div>
                </div>

                {{-- VIII. LANGKAH KERJA --}}
                <div class="space-y-2">
                    <h3 class="text-sm font-bold uppercase">VIII. LANGKAH KERJA</h3>
                    <div>
                        <table class="sig-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 36px;">No</th>
                                    <th rowspan="2">Uraian</th>
                                    <th colspan="2">Dilaksanakan Oleh</th>
                                    <th colspan="2">Waktu Yang Diperlukan</th>
                                </tr>
                                <tr>
                                    <th style="width: 115px;">Rencana</th>
                                    <th style="width: 115px;">Realisasi</th>
                                    <th style="width: 75px;">Rencana</th>
                                    <th style="width: 75px;">Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($langkahKerjas as $idx => $lk)
                                    <tr>
                                        <td class="text-center" style="vertical-align: top;">{{ $idx + 1 }}</td>
                                        <td class="report-section-body" style="vertical-align: top;">{!! $lk->uraian !!}</td>
                                        <td class="text-center" style="vertical-align: top;">
                                            {{ $lk->dilaksanakanRencana?->name ?? '-' }}
                                        </td>
                                        <td class="text-center" style="vertical-align: top;">
                                            {{ $lk->dilaksanakanRealisasi?->name ?? '-' }}
                                        </td>
                                        <td class="text-center" style="vertical-align: top;">
                                            {{ $lk->waktu_rencana !== null ? "{$lk->waktu_rencana} Hari" : '-' }}
                                        </td>
                                        <td class="text-center" style="vertical-align: top;">
                                            {{ $lk->waktu_realisasi !== null ? "{$lk->waktu_realisasi} Hari" : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- IX. PENUTUP --}}
                <div class="space-y-1.5">
                    <h3 class="text-sm font-bold uppercase">IX. PENUTUP</h3>
                    <div class="report-section-body text-sm">
                        @if (! empty(trim(strip_tags((string) $pka->penutup))) || str_contains((string) $pka->penutup, '<table') || str_contains((string) $pka->penutup, '<img'))
                            {!! $pka->penutup !!}
                        @else
                            <p class="italic text-neutral-500">-</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tanggal PKA di bawah Penutup --}}
            <div class="pt-4 text-right text-sm font-semibold">
                Tanggal : {{ $tanggalPkaFormatted }}
            </div>

            {{-- Tabel Tanda Tangan Seluruh Anggota dalam Sprin --}}
            <div class="page-break-inside-avoid pt-4">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th style="width: 36px;">No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th style="width: 80px;">TTD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $index => $member)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="font-semibold">{{ $member['name'] }}</td>
                                <td class="text-center">{{ $member['username'] }}</td>
                                <td>{{ $member['jabatan'] }}</td>
                                <td class="text-center">{{ $member['peran'] }}</td>
                                <td class="text-center" style="width: 80px; height: 42px;">
                                    @if (! empty($member['ttd_url']))
                                        <img
                                            src="{{ $member['ttd_url'] }}"
                                            alt="TTD {{ $member['name'] }}"
                                            style="max-height: 34px; max-width: 68px; margin: 0 auto; object-fit: contain;"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center italic">Belum ada anggota tim pada Sprin ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>
