<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\TabulasiDataTable;
use App\Http\Controllers\Controller;
use App\Models\Pka;
use App\Models\Plotting;
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

        $pkas = Pka::query()
            ->visibleTo($authUser)
            ->where('pkas.status', 'Final')
            ->with([
                'sprin:id,no_sprin,tahap,sprin_tipe',
                'dibuatOleh:id,name,username',
                'direviuOleh:id,name,username',
            ])
            ->orderByDesc('id')
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

        $plottings = Plotting::query()
            ->orderBy('id')
            ->get(['id', 'plot'])
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
            ])
            ->values();

        $provinces = DB::table('wilayah')
            ->whereRaw('LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', 'provinsi as name']);

        return view('audit.tabulasi.index', [
            'dataTableConfig' => $dataTable->config($pkas, $templates, $plottings, $provinces),
            'columns' => $dataTable->columns(),
        ]);
    }
}
