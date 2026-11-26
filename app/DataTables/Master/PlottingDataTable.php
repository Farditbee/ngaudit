<?php

namespace App\DataTables\Master;

use App\Models\Plotting;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PlottingDataTable
{
    /**
     * @return list<array{data: string, name: string, label: string, sortable: bool, searchable: bool, align?: string, width?: string}>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
            ['data' => 'plot', 'name' => 'plot', 'label' => 'NAMA PLOT', 'sortable' => true, 'searchable' => true, 'width' => 'w-48'],
            ['data' => 'provinces_names', 'name' => 'provinces_names', 'label' => 'DAFTAR PROVINSI', 'sortable' => false, 'searchable' => true],
            ['data' => 'provinces_count', 'name' => 'provinces_count', 'label' => 'JUMLAH PROVINSI', 'sortable' => true, 'searchable' => false, 'align' => 'center', 'width' => 'w-40'],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-32'],
        ];
    }

    /**
     * @param  iterable<int, object>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $provinces): array
    {
        return [
            'indexUrl' => route('master.plotting.index'),
            'storeUrl' => route('master.plotting.store'),
            'baseUrl' => url('/master/plotting'),
            'provinces' => $provinces,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $provinsiId = (int) $request->query('provinsi_id', 0);

        $query = Plotting::query()
            ->with(['provinces:id,kode_wilayah,provinsi'])
            ->withCount('provinces');

        if ($provinsiId > 0) {
            $query->whereHas('provinces', function (Builder $builder) use ($provinsiId): void {
                $builder->whereKey($provinsiId);
            });
        }

        if (! $request->has('order')) {
            $query->orderByDesc('plottings.created_at')->orderByDesc('plottings.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('provinces_list', fn (Plotting $plotting): array => $plotting->provinces->map(
                fn (Wilayah $province): array => [
                    'id' => (int) $province->id,
                    'code' => (string) $province->kode_wilayah,
                    'name' => (string) $province->provinsi,
                ]
            )->values()->all())
            ->addColumn('provinsi_ids', fn (Plotting $plotting): array => $plotting->provinces->pluck('id')->map(
                fn (mixed $id): int => (int) $id
            )->values()->all())
            ->addColumn('provinces_names', fn (Plotting $plotting): string => $plotting->provinces->pluck('provinsi')->implode(', '))
            ->filterColumn('provinces_names', function (Builder $query, string $keyword): void {
                $query->whereHas('provinces', function (Builder $builder) use ($keyword): void {
                    $builder->where('provinsi', 'like', "%{$keyword}%")
                        ->orWhere('kode_wilayah', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
