<?php

namespace App\Http\Controllers\Anev;

use App\Http\Controllers\Controller;
use App\Models\StrukturPerangkatDaerah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnevTabulasiController extends Controller
{
    /**
     * Tampilkan Anev Tabulasi Per Bidang untuk lingkup Dinas.
     */
    public function dinas(Request $request): View
    {
        return $this->renderView($request, 'Dinas');
    }

    /**
     * Tampilkan Anev Tabulasi Per Bidang untuk lingkup Kecamatan.
     */
    public function kecamatan(Request $request): View
    {
        return $this->renderView($request, 'Kecamatan');
    }

    /**
     * Render view Anev Tabulasi Per Bidang.
     */
    protected function renderView(Request $request, string $lingkup): View
    {
        $filters = [
            'dari' => $request->query('dari'),
            'sampai' => $request->query('sampai'),
            'jenis_audit' => $request->query('jenis_audit'),
        ];

        $tahap1Data = $this->getRekapData($lingkup, '1', $filters);
        $tahap2Data = $this->getRekapData($lingkup, '2', $filters);

        $activeTahap = (int) $request->query('tahap', 1);
        if (! in_array($activeTahap, [1, 2], true)) {
            $activeTahap = 1;
        }

        return view('anev.tabulasi-bidang', [
            'lingkup' => $lingkup,
            'activeTahap' => $activeTahap,
            'filters' => $filters,
            'tahap1' => $tahap1Data,
            'tahap2' => $tahap2Data,
        ]);
    }

    /**
     * Hitung rekapitulasi jumlah temuan per bidang untuk lingkup dan tahap tertentu.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     tahap: string,
     *     tahap_roman: string,
     *     date_range: string,
     *     rows: list<array{bidang: string, belum: int, sudah: int, koreksi: int, belum_selesai: int, selesai: int, total: int}>,
     *     total: array{belum: int, sudah: int, koreksi: int, belum_selesai: int, selesai: int, total: int},
     *     satker_rows: list<array<string, mixed>>,
     *     satker_total: array{opsnal: int, sdm: int, logistik: int, garkeu: int, jumlah: int}
     * }
     */
    protected function getRekapData(string $lingkup, string $tahap, array $filters = []): array
    {
        // 1. Rentang tanggal dari Sprin
        $sprinDates = DB::table('sprins')
            ->whereNull('deleted_at')
            ->where('sprin_tipe', $lingkup)
            ->where('tahap', $tahap)
            ->selectRaw('MIN(tgl_mulai) as min_mulai, MAX(tgl_akhir) as max_akhir')
            ->first();

        $dateRange = '-';
        if ($sprinDates && $sprinDates->min_mulai && $sprinDates->max_akhir) {
            $minMulai = Carbon::parse($sprinDates->min_mulai)->format('Y-m-d');
            $maxAkhir = Carbon::parse($sprinDates->max_akhir)->format('Y-m-d');
            $dateRange = "{$minMulai} s.d {$maxAkhir}";
        }

        // 2. Ambil master semua Dinas atau Kecamatan sesuai lingkup
        $masterQuery = StrukturPerangkatDaerah::query()->where('level', 1);
        if ($lingkup === 'Kecamatan') {
            $masterQuery->where('jenis_unit', 'Kecamatan');
        } else {
            $masterQuery->whereIn('jenis_unit', ['Dinas', 'Badan', 'Inspektorat', 'Sekretariat Daerah', 'Sekretariat DPRD', 'Staf Ahli']);
        }
        $masterSatkers = $masterQuery->orderBy('nama_unit')->get(['id', 'nama_unit']);
        $masterIds = $masterSatkers->pluck('id')->all();

        // Ambil sub unit (level 2) untuk seluruh master satker
        $allSubUnits = StrukturPerangkatDaerah::query()
            ->whereIn('parent_id', $masterIds)
            ->whereNull('deleted_at')
            ->orderBy('nama_unit')
            ->get(['id', 'parent_id', 'nama_unit', 'jenis_unit', 'level']);

        $subsByParent = $allSubUnits->groupBy('parent_id');

        $satkerMap = [];
        foreach ($masterSatkers as $unit) {
            $sName = trim((string) $unit->nama_unit);
            $subMap = [];
            if (isset($subsByParent[$unit->id])) {
                foreach ($subsByParent[$unit->id] as $child) {
                    $cName = trim((string) $child->nama_unit);
                    $subMap[$cName] = [
                        'id' => $child->id,
                        'nama_sub' => $cName,
                        'opsnal' => 0,
                        'sdm' => 0,
                        'logistik' => 0,
                        'garkeu' => 0,
                        'jumlah' => 0,
                    ];
                }
            }

            $satkerMap[$sName] = [
                'id' => $unit->id,
                'satker' => $sName,
                'opsnal' => 0,
                'sdm' => 0,
                'logistik' => 0,
                'garkeu' => 0,
                'jumlah' => 0,
                '_sub_map' => $subMap,
            ];
        }

        // Pastikan satker dari Sprin juga terdaftar jika ada yang belum tercakup
        $sprinList = DB::table('sprins')
            ->whereNull('sprins.deleted_at')
            ->where('sprins.sprin_tipe', $lingkup)
            ->where('sprins.tahap', $tahap)
            ->leftJoin('struktur_perangkat_daerah as spd_sprin', 'spd_sprin.id', '=', 'sprins.struktur_perangkat_daerah_id')
            ->select([
                'sprins.id',
                'sprins.struktur_perangkat_daerah_id',
                'spd_sprin.nama_unit as satker_sprin',
            ])
            ->get();

        foreach ($sprinList as $sprin) {
            $sName = $sprin->satker_sprin ? trim((string) $sprin->satker_sprin) : null;
            if ($sName && ! isset($satkerMap[$sName])) {
                $satkerMap[$sName] = [
                    'id' => $sprin->struktur_perangkat_daerah_id,
                    'satker' => $sName,
                    'opsnal' => 0,
                    'sdm' => 0,
                    'logistik' => 0,
                    'garkeu' => 0,
                    'jumlah' => 0,
                    '_sub_map' => [],
                ];
            }
        }

        // 3. Query tabulasi temuan
        $tabulasiQuery = DB::table('kka_tabulasis')
            ->where('kka_tabulasis.jenis_tabulasi', 'Temuan')
            ->whereNull('kka_tabulasis.deleted_at')
            ->join('kkas', 'kkas.id', '=', 'kka_tabulasis.kka_id')
            ->whereNull('kkas.deleted_at')
            ->join('pkas', 'pkas.id', '=', 'kkas.pka_id')
            ->whereNull('pkas.deleted_at')
            ->join('sprins', 'sprins.id', '=', 'pkas.sprin_id')
            ->whereNull('sprins.deleted_at')
            ->where('sprins.sprin_tipe', $lingkup)
            ->where('sprins.tahap', $tahap);

        // Filter tanggal pembuatan KKA (Dari)
        if (! empty($filters['dari'])) {
            $tabulasiQuery->whereDate(
                DB::raw('COALESCE(kkas.tanggal_dibuat, kkas.tanggal_kka, kkas.created_at)'),
                '>=',
                $filters['dari']
            );
        }

        // Filter tanggal pembuatan KKA (Sampai)
        if (! empty($filters['sampai'])) {
            $tabulasiQuery->whereDate(
                DB::raw('COALESCE(kkas.tanggal_dibuat, kkas.tanggal_kka, kkas.created_at)'),
                '<=',
                $filters['sampai']
            );
        }

        // Filter jenis audit
        if (! empty($filters['jenis_audit']) && $filters['jenis_audit'] !== 'Semua') {
            $jenis = $filters['jenis_audit'];
            $tabulasiQuery->where(function ($q) use ($jenis) {
                $q->where('pkas.jenis_audit', $jenis)
                    ->orWhere('pkas.jenis_audit', 'like', "%{$jenis}%");
            });
        }

        $tabulasis = $tabulasiQuery
            ->leftJoin('struktur_perangkat_daerah as spd_sprin', 'spd_sprin.id', '=', 'sprins.struktur_perangkat_daerah_id')
            ->leftJoin('struktur_perangkat_daerah as spd_kka', 'spd_kka.id', '=', 'kkas.struktur_perangkat_daerah_id')
            ->leftJoin('struktur_perangkat_daerah as spd_kka_parent', 'spd_kka_parent.id', '=', 'spd_kka.parent_id')
            ->select([
                'kka_tabulasis.id',
                'kka_tabulasis.status_tinjut',
                'kka_tabulasis.tindak_lanjut',
                'pkas.bidang',
                'pkas.sasaran_auditi',
                'spd_sprin.nama_unit as satker_sprin',
                'spd_kka.nama_unit as satker_kka',
                'spd_kka.level as spd_kka_level',
                'spd_kka_parent.nama_unit as satker_kka_parent',
            ])
            ->get();

        // 4. Mapping 4 Bidang standar
        $bidangMap = [
            'OPERASIONAL' => ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0],
            'SDM' => ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0],
            'LOGISTIK' => ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0],
            'GARKEU' => ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0],
        ];

        foreach ($tabulasis as $t) {
            $key = $this->normalizeBidang((string) $t->bidang);
            if (! isset($bidangMap[$key])) {
                $bidangMap[$key] = ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0];
            }

            $isSudah = in_array($t->status_tinjut, ['Sudah Ditinjut', 'Dalam Proses', 'Koreksi', 'Belum Sesuai', 'Selesai'], true)
                || (! empty($t->tindak_lanjut) && trim(strip_tags((string) $t->tindak_lanjut)) !== '');

            $bidangMap[$key]['total']++;

            if ($isSudah) {
                $bidangMap[$key]['sudah']++;
                if ($t->status_tinjut === 'Selesai') {
                    $bidangMap[$key]['selesai']++;
                } else {
                    $bidangMap[$key]['koreksi']++;
                    $bidangMap[$key]['belum_selesai']++;
                }
            } else {
                $bidangMap[$key]['belum']++;
            }

            // Hitung ke Satker
            $satkerName = $this->resolveSatkerName(
                $t->satker_sprin,
                $t->satker_kka,
                $t->spd_kka_level !== null ? (int) $t->spd_kka_level : null,
                $t->satker_kka_parent,
                $t->sasaran_auditi
            );

            if (! isset($satkerMap[$satkerName])) {
                $satkerMap[$satkerName] = [
                    'id' => null,
                    'satker' => $satkerName,
                    'opsnal' => 0,
                    'sdm' => 0,
                    'logistik' => 0,
                    'garkeu' => 0,
                    'jumlah' => 0,
                    '_sub_map' => [],
                ];
            }

            // Tentukan sub unit untuk temuan ini
            $subName = 'Unit Induk';
            if (! empty($t->satker_kka) && (int) $t->spd_kka_level > 1) {
                $subName = trim((string) $t->satker_kka);
            } elseif (! empty($t->satker_kka_parent) && (int) $t->spd_kka_level > 2) {
                $subName = trim((string) $t->satker_kka_parent);
            }

            if (! isset($satkerMap[$satkerName]['_sub_map'][$subName])) {
                $satkerMap[$satkerName]['_sub_map'][$subName] = [
                    'id' => null,
                    'nama_sub' => $subName,
                    'opsnal' => 0,
                    'sdm' => 0,
                    'logistik' => 0,
                    'garkeu' => 0,
                    'jumlah' => 0,
                ];
            }

            match ($key) {
                'OPERASIONAL' => [
                    $satkerMap[$satkerName]['opsnal']++,
                    $satkerMap[$satkerName]['_sub_map'][$subName]['opsnal']++,
                ],
                'SDM' => [
                    $satkerMap[$satkerName]['sdm']++,
                    $satkerMap[$satkerName]['_sub_map'][$subName]['sdm']++,
                ],
                'LOGISTIK' => [
                    $satkerMap[$satkerName]['logistik']++,
                    $satkerMap[$satkerName]['_sub_map'][$subName]['logistik']++,
                ],
                'GARKEU' => [
                    $satkerMap[$satkerName]['garkeu']++,
                    $satkerMap[$satkerName]['_sub_map'][$subName]['garkeu']++,
                ],
                default => null,
            };
            $satkerMap[$satkerName]['jumlah']++;
            $satkerMap[$satkerName]['_sub_map'][$subName]['jumlah']++;
        }

        $rows = [];
        $total = ['belum' => 0, 'sudah' => 0, 'koreksi' => 0, 'belum_selesai' => 0, 'selesai' => 0, 'total' => 0];

        foreach ($bidangMap as $bName => $counts) {
            $rows[] = array_merge(['bidang' => $bName], $counts);
            $total['belum'] += $counts['belum'];
            $total['sudah'] += $counts['sudah'];
            $total['koreksi'] += $counts['koreksi'];
            $total['belum_selesai'] += $counts['belum_selesai'];
            $total['selesai'] += $counts['selesai'];
            $total['total'] += $counts['total'];
        }

        // Satker Rows & Total
        ksort($satkerMap);
        $satkerRows = [];
        $satkerTotal = [
            'opsnal' => 0,
            'sdm' => 0,
            'logistik' => 0,
            'garkeu' => 0,
            'jumlah' => 0,
        ];

        $no = 1;
        foreach ($satkerMap as $sCounts) {
            $subMap = $sCounts['_sub_map'] ?? [];
            unset($sCounts['_sub_map']);

            ksort($subMap);
            $subRows = [];
            $subNo = 1;
            foreach ($subMap as $subCounts) {
                $subRows[] = array_merge(['no' => $subNo++], $subCounts);
            }

            if (empty($subRows)) {
                $subRows[] = [
                    'no' => 1,
                    'nama_sub' => 'Unit Induk',
                    'opsnal' => $sCounts['opsnal'],
                    'sdm' => $sCounts['sdm'],
                    'logistik' => $sCounts['logistik'],
                    'garkeu' => $sCounts['garkeu'],
                    'jumlah' => $sCounts['jumlah'],
                ];
            }

            $satkerRows[] = array_merge(['no' => $no++], $sCounts, ['sub_units' => $subRows]);
            $satkerTotal['opsnal'] += $sCounts['opsnal'];
            $satkerTotal['sdm'] += $sCounts['sdm'];
            $satkerTotal['logistik'] += $sCounts['logistik'];
            $satkerTotal['garkeu'] += $sCounts['garkeu'];
            $satkerTotal['jumlah'] += $sCounts['jumlah'];
        }

        $tahapRoman = match ((string) $tahap) {
            '1' => 'TAHAP I',
            '2' => 'TAHAP II',
            '3' => 'TAHAP III',
            default => 'TAHAP '.$tahap,
        };

        return [
            'tahap' => $tahap,
            'tahap_roman' => $tahapRoman,
            'date_range' => $dateRange,
            'rows' => $rows,
            'total' => $total,
            'satker_rows' => $satkerRows,
            'satker_total' => $satkerTotal,
        ];
    }

    /**
     * Normalisasi nama bidang ke 4 kategori standar.
     */
    protected function normalizeBidang(string $bidang): string
    {
        $lower = strtolower(trim($bidang));

        if (str_contains($lower, 'operasional')) {
            return 'OPERASIONAL';
        }

        if (str_contains($lower, 'sumber daya manusia') || str_contains($lower, 'sdm')) {
            return 'SDM';
        }

        if (str_contains($lower, 'logistik')) {
            return 'LOGISTIK';
        }

        if (str_contains($lower, 'anggaran') || str_contains($lower, 'keuangan') || str_contains($lower, 'garkeu')) {
            return 'GARKEU';
        }

        return ! empty($bidang) ? strtoupper($bidang) : 'OPERASIONAL';
    }

    /**
     * Tentukan nama Satker (Dinas / Kecamatan) berdasarkan data Sprin / KKA / PKA.
     */
    protected function resolveSatkerName(?string $satkerSprin, ?string $satkerKka, ?int $kkaLevel, ?string $satkerKkaParent, ?string $sasaranAuditi): string
    {
        if (! empty($satkerSprin)) {
            return trim($satkerSprin);
        }

        if (! empty($satkerKka) && ($kkaLevel === 1 || empty($satkerKkaParent))) {
            return trim($satkerKka);
        }

        if (! empty($satkerKkaParent)) {
            return trim($satkerKkaParent);
        }

        if (! empty($satkerKka)) {
            return trim($satkerKka);
        }

        if (! empty($sasaranAuditi)) {
            return trim($sasaranAuditi);
        }

        return 'Satker Lainnya';
    }
}
