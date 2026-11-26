<?php

namespace App\DataTables\Master;

use App\Models\Kriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KriteriaDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-14'],
            ['data' => 'nama', 'name' => 'nama', 'label' => 'NAMA KRITERIA', 'sortable' => true, 'searchable' => true],
            ['data' => 'is_opsnal', 'name' => 'is_opsnal', 'label' => 'OPSNAL', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'is_logistik', 'name' => 'is_logistik', 'label' => 'LOGISTIK', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
            ['data' => 'is_sdm', 'name' => 'is_sdm', 'label' => 'SDM', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-20'],
            ['data' => 'is_garkeu', 'name' => 'is_garkeu', 'label' => 'GARKEU', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-24'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'indexUrl' => route('master.kriteria.index'),
            'storeUrl' => route('master.kriteria.store'),
            'baseUrl' => url('/master/kriteria'),
            'columns' => $this->columns(),
            'defaultSortCol' => 1,
            'defaultSortDir' => 'asc',
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $bidang = (string) $request->query('bidang', '');

        $query = Kriteria::query();

        if (in_array($bidang, ['is_opsnal', 'is_logistik', 'is_sdm', 'is_garkeu'], true)) {
            $query->where($bidang, true);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('name', fn (Kriteria $kriteria): string => $kriteria->nama)
            ->toJson();
    }
}
