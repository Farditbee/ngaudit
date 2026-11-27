@php
    $noKka = $kka->nomor_kka ?: ('KKA '.$kka->tanggal_kka?->format('d/m/Y'));
    $dibuatUser = $kka->dibuatOleh ?? $kka->creator;
    $direviuUser = $kka->direviuOleh ?? $sprin?->ketuaTim;
    $sections = [
        ['label' => 'I. TUJUAN AUDIT', 'content' => $kka->tujuan],
        ['label' => 'II. LANGKAH KERJA', 'content' => $kka->langkah],
        ['label' => 'III. HASIL PENGUJIAN', 'content' => $kka->hasil],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak KKA - {{ $noKka }}</title>
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

        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
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
        }
    </style>
</head>
<body>
    {{-- Floating Action Bar --}}
    <div class="no-print sticky top-0 z-50 flex items-center justify-between border-b border-neutral-200 bg-white/95 px-6 py-3 shadow-xs backdrop-blur">
        <div class="flex items-center gap-3 font-sans">
            <span class="text-sm font-bold text-neutral-900">Pratinjau Cetak PDF KKA</span>
            <span class="rounded-md bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-700">{{ $noKka }}</span>
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

    <div class="report-sheet">
        <div class="report-content space-y-5">
            <div class="text-center">
                <h1 class="text-base font-bold uppercase leading-snug tracking-wide">
                    KERTAS KERJA AUDIT (KKA) — {{ strtoupper($pka?->bidang ?? '-') }}
                </h1>
                <h2 class="text-base font-bold uppercase leading-snug tracking-wide">
                    AUDITEE : {{ strtoupper($auditeeName) }}
                </h2>
                <div class="my-2.5 border-b-2 border-black"></div>
                <div class="text-center text-sm font-bold">
                    Nomor KKA : {{ $noKka }}
                </div>
            </div>

            <div class="pt-2">
                <table class="meta-table text-sm">
                    <tbody>
                        <tr>
                            <td class="w-40 font-semibold">Nomor Sprin</td>
                            <td class="w-4">:</td>
                            <td>{{ $sprin?->no_sprin ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Nomor PKA</td>
                            <td>:</td>
                            <td>{{ $pka?->nomor_pka ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Dibuat oleh</td>
                            <td>:</td>
                            <td>{{ $dibuatUser?->name ?? '-' }} ({{ $kka->tanggal_dibuat?->format('d/m/Y') ?? '-' }})</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Direviu oleh</td>
                            <td>:</td>
                            <td>{{ $direviuUser?->name ?? '-' }} ({{ $kka->tanggal_direviu?->format('d/m/Y') ?? '-' }})</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Tempat & Tanggal</td>
                            <td>:</td>
                            <td>{{ $kka->tempat ?? '-' }}, {{ $kka->tanggal_kka?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 pt-2">
                @foreach ($sections as $section)
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
            </div>
        </div>
    </div>
</body>
</html>
