@php
    $noKka = $kka->nomor_kka ?: ('KKA '.$kka->tanggal_kka?->format('d/m/Y'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak PTL - {{ $noKka }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            color: #111827;
            background: #f3f4f6;
            font-size: 10pt;
        }

        .report-sheet {
            max-width: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 14mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }

        .ptl-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .ptl-table th,
        .ptl-table td {
            border: 1px solid #111827;
            padding: 5px 6px;
            vertical-align: top;
        }

        .ptl-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        @media print {
            body { background: #ffffff !important; }
            .no-print { display: none !important; }
            .report-sheet { margin: 0 !important; padding: 0 !important; max-width: 100% !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print sticky top-0 z-50 flex items-center justify-between border-b border-neutral-200 bg-white/95 px-6 py-3 shadow-xs backdrop-blur font-sans">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-neutral-900">Pemantauan Tindak Lanjut (PTL)</span>
            <span class="rounded-md bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-700">{{ $noKka }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex h-9 items-center gap-2 rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700">
                <span>Cetak / Simpan PDF</span>
            </button>
            <button type="button" onclick="window.close(); if (!window.closed) { window.history.back(); }" class="inline-flex h-9 items-center rounded-md border border-neutral-300 bg-white px-3.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">
                Tutup
            </button>
        </div>
    </div>

    <div class="report-sheet space-y-4">
        <div class="text-center">
            <h1 class="text-base font-bold uppercase">MATRIKS PEMANTAUAN TINDAK LANJUT (PTL) HASIL AUDIT</h1>
            <p class="text-xs font-semibold">Nomor KKA: {{ $noKka }} | Nomor PKA: {{ $pka?->nomor_pka ?? '-' }} | Nomor Sprin: {{ $sprin?->no_sprin ?? '-' }}</p>
        </div>

        <table class="ptl-table">
            <thead>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th>TEMUAN / PERMASALAHAN</th>
                    <th style="width: 65px;">KODE TEMUAN</th>
                    <th>KRITERIA</th>
                    <th style="width: 90px;">NILAI</th>
                    <th>SEBAB</th>
                    <th>AKIBAT</th>
                    <th>REKOMENDASI</th>
                    <th>TINDAK LANJUT AUDITEE</th>
                    <th style="width: 80px;">KESIMPULAN</th>
                    <th style="width: 80px;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kka->tabulasis as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->nomor }}</td>
                        <td>{!! $item->uraian_temuan ?: '-' !!}</td>
                        <td style="text-align: center;">
                            {{ $item->kodeTemuan?->kode ?? '-' }}
                            @if ($item->subKodeTemuan?->kode)
                                <br><small>Sub: {{ $item->subKodeTemuan->kode }}</small>
                            @endif
                        </td>
                        <td>{!! $item->uraian_kriteria ?: '-' !!}</td>
                        <td>{{ $item->nilai_temuan !== null ? 'IDR '.number_format((float) $item->nilai_temuan, 0, ',', '.') : '-' }}</td>
                        <td>{!! $item->uraian_sebab ?: '-' !!}</td>
                        <td>{!! $item->uraian_akibat ?: '-' !!}</td>
                        <td>{!! $item->uraian_rekomendasi ?: '-' !!}</td>
                        <td>{!! $item->tindak_lanjut ?: '-' !!}</td>
                        <td>{{ $item->kesimpulan_tinjut ?: '-' }}</td>
                        <td style="text-align: center;">{{ $item->status_tinjut ?: 'Belum Ditinjut' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 16px;">Belum ada data tabulasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
