<?php

namespace App\DataTables\Audit;

use App\Models\Pka;
use App\Models\PkaLangkahKerja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PkaLangkahKerjaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'checkbox', 'name' => 'checkbox', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-10'],
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => 'NO', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'uraian', 'name' => 'pka_langkah_kerjas.uraian', 'label' => 'URAIAN', 'sortable' => true, 'searchable' => true],
            ['data' => 'rencana_name', 'name' => 'rencana.name', 'label' => 'RENCANA', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'realisasi_name', 'name' => 'realisasi.name', 'label' => 'REALISASI', 'sortable' => true, 'searchable' => true, 'align' => 'center', 'width' => 'w-36'],
            ['data' => 'waktu_rencana', 'name' => 'pka_langkah_kerjas.waktu_rencana', 'label' => 'RENCANA', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'waktu_realisasi', 'name' => 'pka_langkah_kerjas.waktu_realisasi', 'label' => 'REALISASI', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-20'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $auditors
     * @param  iterable<int, mixed>  $templates
     * @return array<string, mixed>
     */
    public function config(Pka $pka, iterable $auditors, iterable $templates): array
    {
        return [
            'pkaId' => $pka->id,
            'nomorPka' => $pka->nomor_pka,
            'bidang' => $pka->bidang,
            'backUrl' => route('audit.pka.index'),
            'pkaShowUrl' => route('audit.pka.show', $pka),
            'indexUrl' => route('audit.pka.langkah-kerja.index', $pka),
            'storeUrl' => route('audit.pka.langkah-kerja.store', $pka),
            'applyTemplateUrl' => route('audit.pka.langkah-kerja.apply-template', $pka),
            'bulkDeleteUrl' => route('audit.pka.langkah-kerja.bulk-destroy', $pka),
            'clearAllUrl' => route('audit.pka.langkah-kerja.clear', $pka),
            'baseUrl' => url("/audit/pka/{$pka->id}/langkah-kerja"),
            'auditors' => $auditors,
            'templates' => $templates,
            'columns' => $this->columns(),
            'defaultSortCol' => null,
            'defaultSortDir' => 'asc',
        ];
    }

    public function toJson(Request $request, Pka $pka): JsonResponse
    {
        $query = PkaLangkahKerja::query()
            ->where('pka_langkah_kerjas.pka_id', $pka->id)
            ->leftJoin('users as rencana', 'rencana.id', '=', 'pka_langkah_kerjas.dilaksanakan_rencana_id')
            ->leftJoin('users as realisasi', 'realisasi.id', '=', 'pka_langkah_kerjas.dilaksanakan_realisasi_id')
            ->select([
                'pka_langkah_kerjas.*',
                'rencana.name as rencana_name',
                'realisasi.name as realisasi_name',
            ]);

        if (! $request->has('order')) {
            $query->orderBy('pka_langkah_kerjas.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('rencana_name', fn (PkaLangkahKerja $l): string => $l->rencana_name ?? $l->dilaksanakanRencana?->name ?? '-')
            ->addColumn('realisasi_name', fn (PkaLangkahKerja $l): string => $l->realisasi_name ?? $l->dilaksanakanRealisasi?->name ?? '-')
            ->addColumn('waktu_rencana_formatted', fn (PkaLangkahKerja $l): string => $l->waktu_rencana !== null ? "{$l->waktu_rencana} Hari" : '-')
            ->addColumn('waktu_realisasi_formatted', fn (PkaLangkahKerja $l): string => $l->waktu_realisasi !== null ? "{$l->waktu_realisasi} Hari" : '-')
            ->rawColumns(['uraian'])
            ->filterColumn('rencana_name', function (Builder $query, string $keyword): void {
                $query->where('rencana.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('realisasi_name', function (Builder $query, string $keyword): void {
                $query->where('realisasi.name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }
}
