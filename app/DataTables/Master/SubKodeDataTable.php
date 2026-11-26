<?php

namespace App\DataTables\Master;

use App\Models\Kode;
use App\Models\SubKode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubKodeDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
            ['data' => 'parent_label', 'name' => 'parent_label', 'label' => 'KODE INDUK', 'sortable' => false, 'searchable' => true],
            ['data' => 'kode', 'name' => 'kode', 'label' => 'SUB KODE', 'sortable' => true, 'searchable' => true, 'width' => 'w-32'],
            ['data' => 'nama', 'name' => 'nama', 'label' => 'NAMA', 'sortable' => true, 'searchable' => true],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-32'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function config(string $jenis): array
    {
        $parents = Kode::query()
            ->where('jenis', $jenis)
            ->with('kodeTemuan:id,kode,nama')
            ->orderBy('kode')
            ->get(['id', 'kode_temuan_id', 'kode', 'nama'])
            ->map(fn (Kode $kode): array => [
                'id' => $kode->id,
                'code' => (string) $kode->id,
                'name' => $kode->label(),
            ])
            ->values()
            ->all();

        return [
            'indexUrl' => route('master.sub-kode.index', $jenis),
            'storeUrl' => route('master.sub-kode.store', $jenis),
            'baseUrl' => url("/master/sub-kode/{$jenis}"),
            'isSub' => true,
            'requiresTemuan' => false,
            'parents' => $parents,
            'columns' => $this->columns(),
            'defaultSortCol' => 2,
            'defaultSortDir' => 'asc',
        ];
    }

    public function toJson(Request $request, string $jenis): JsonResponse
    {
        $kodeId = (int) $request->query('kode_id', 0);

        $query = SubKode::query()
            ->select('sub_kodes.*')
            ->with('parent:id,kode_temuan_id,kode,nama')
            ->whereHas('parent', fn (Builder $builder) => $builder->where('jenis', $jenis));

        if ($kodeId > 0) {
            $query->where('sub_kodes.kode_id', $kodeId);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('parent_label', fn (SubKode $subKode): string => $subKode->parent?->label() ?? '-')
            ->addColumn('name', fn (SubKode $subKode): string => $subKode->kode.' - '.$subKode->nama)
            ->filterColumn('parent_label', function (Builder $query, string $keyword): void {
                $query->whereHas('parent', function (Builder $builder) use ($keyword): void {
                    $builder->where('kode', 'like', "%{$keyword}%")
                        ->orWhere('nama', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
