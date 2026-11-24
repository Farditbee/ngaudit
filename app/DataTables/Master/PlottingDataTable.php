<?php

namespace App\DataTables\Master;

use App\Models\Plotting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PlottingDataTable
{
    public function toJson(Request $request): JsonResponse
    {
        $provinsiId = (int) $request->query('provinsi_id', 0);

        $query = Plotting::query()
            ->with(['provinces:id,code,name'])
            ->withCount('provinces');

        if ($provinsiId > 0) {
            $query->whereHas('provinces', function (Builder $builder) use ($provinsiId): void {
                $builder->whereKey($provinsiId);
            });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('provinces_list', fn (Plotting $plotting): array => $plotting->provinces->map(
                fn (object $province): array => [
                    'id' => (int) $province->id,
                    'code' => (string) $province->code,
                    'name' => (string) $province->name,
                ]
            )->values()->all())
            ->addColumn('provinsi_ids', fn (Plotting $plotting): array => $plotting->provinces->pluck('id')->map(
                fn (mixed $id): int => (int) $id
            )->values()->all())
            ->addColumn('provinces_names', fn (Plotting $plotting): string => $plotting->provinces->pluck('name')->implode(', '))
            ->filterColumn('provinces_names', function (Builder $query, string $keyword): void {
                $query->whereHas('provinces', function (Builder $builder) use ($keyword): void {
                    $builder->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
