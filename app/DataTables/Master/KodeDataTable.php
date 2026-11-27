<?php

namespace App\DataTables\Master;

use App\Models\Kode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KodeDataTable
{
    /**
     * @return list<array<string, mixed>>
     */
    public function columns(string $jenis): array
    {
        $columns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
        ];

        if (Kode::requiresTemuan($jenis)) {
            $columns[] = ['data' => 'temuan_label', 'name' => 'temuan_label', 'label' => 'KODE TEMUAN', 'sortable' => false, 'searchable' => true];
        }

        $columns[] = ['data' => 'kode', 'name' => 'kode', 'label' => 'KODE', 'sortable' => true, 'searchable' => true, 'width' => 'w-32'];
        $columns[] = ['data' => 'nama', 'name' => 'nama', 'label' => 'NAMA', 'sortable' => true, 'searchable' => true];
        $columns[] = ['data' => 'sub_kodes_count', 'name' => 'sub_kodes_count', 'label' => 'JUMLAH SUB KODE', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-40'];
        $columns[] = ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-32'];

        return $columns;
    }

    /**
     * @return array<string, mixed>
     */
    public function config(string $jenis): array
    {
        $requiresTemuan = Kode::requiresTemuan($jenis);

        $parents = $requiresTemuan
            ? Kode::query()
                ->where('jenis', Kode::JENIS_TEMUAN)
                ->orderBy('kode')
                ->get(['id', 'kode', 'nama'])
                ->map(fn (Kode $kode): array => [
                    'id' => $kode->id,
                    'code' => (string) $kode->id,
                    'name' => $kode->label(),
                ])
                ->values()
                ->all()
            : [];

        return [
            'indexUrl' => route('master.kode.index', $jenis),
            'storeUrl' => route('master.kode.store', $jenis),
            'baseUrl' => url("/master/kode/{$jenis}"),
            'isSub' => false,
            'requiresTemuan' => $requiresTemuan,
            'parents' => $parents,
            'columns' => $this->columns($jenis),
            'defaultSortCol' => $requiresTemuan ? 2 : 1,
            'defaultSortDir' => 'asc',
        ];
    }

    public function toJson(Request $request, string $jenis): JsonResponse
    {
        $kodeTemuanId = (int) $request->query('kode_temuan_id', 0);

        $query = Kode::query()
            ->select('kodes.*')
            ->where('kodes.jenis', $jenis)
            ->with('kodeTemuan:id,kode,nama')
            ->withCount('subKodes');

        if (Kode::requiresTemuan($jenis) && $kodeTemuanId > 0) {
            $query->where('kodes.kode_temuan_id', $kodeTemuanId);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('temuan_label', fn (Kode $kode): string => $kode->kodeTemuan?->label() ?? '-')
            ->addColumn('name', fn (Kode $kode): string => $kode->label())
            ->filterColumn('temuan_label', function (Builder $query, string $keyword): void {
                $query->whereHas('kodeTemuan', function (Builder $builder) use ($keyword): void {
                    $builder->where('kode', 'like', "%{$keyword}%")
                        ->orWhere('nama', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
