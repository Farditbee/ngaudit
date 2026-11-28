<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\TabulasiDataTable;
use App\Http\Controllers\Controller;
use App\Models\Pka;
use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TabulasiController extends Controller
{
    public function index(Request $request, TabulasiDataTable $dataTable): View|JsonResponse
    {
        if ($request->filled('options')) {
            return app(KkaController::class)->index($request, $dataTable);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $authUser = $request->user();

        $formatUser = fn (?User $u): ?string => $u
            ? ($u->username ? "{$u->username} | {$u->name}" : (string) $u->name)
            : null;

        $bidangCaseSql = "CASE pkas.bidang 
            WHEN 'Operasional' THEN 1 
            WHEN 'Manajemen Anggaran dan Keuangan' THEN 2 
            WHEN 'Logistik' THEN 3 
            WHEN 'Sumber Daya Manusia' THEN 4 
            WHEN 'Investigasi' THEN 5 
            ELSE 6 END";

        $pkas = Pka::query()
            ->visibleTo($authUser)
            ->where('pkas.status', 'Final')
            ->with([
                'sprin:id,no_sprin,tahap,sprin_tipe',
                'dibuatOleh:id,name,username',
                'direviuOleh:id,name,username',
            ])
            ->orderByRaw($bidangCaseSql)
            ->orderByDesc('pkas.id')
            ->get()
            ->map(fn (Pka $pka): array => [
                'id' => $pka->id,
                'code' => (string) $pka->id,
                'name' => $pka->nomor_pka.' ('.($pka->sprin?->no_sprin ?? '-').')',
                'nomor_pka' => $pka->nomor_pka,
                'no_sprin' => $pka->sprin?->no_sprin ?? '-',
                'tahap' => $pka->sprin?->tahap,
                'bidang' => $pka->bidang,
                'jenis_audit' => $pka->jenis_audit,
                'dibuat_oleh_name' => $formatUser($pka->dibuatOleh),
                'direviu_oleh_name' => $formatUser($pka->direviuOleh),
            ])
            ->values();

        $templates = TemplateKka::query()
            ->visibleTo($authUser)
            ->orderBy('nama_template')
            ->get(['id', 'nama_template', 'lingkup', 'tujuan', 'langkah', 'hasil']);

        $plottings = collect();

        $targetWilayahTable = 'struktur_perangkat_daerah';

        $provinces = DB::table($targetWilayahTable)
            ->whereNotNull('kode_wilayah')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', 'nama_unit as name']);

        return view('audit.tabulasi.index', [
            'dataTableConfig' => $dataTable->config($pkas, $templates, $plottings, $provinces),
            'columns' => $dataTable->columns(),
        ]);
    }
}
