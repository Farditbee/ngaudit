<?php

namespace App\DataTables\Master;

use App\Models\Tim;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TimDataTable
{
    /**
     * @return list<array{data: string, name: string, label: string, sortable: bool, searchable: bool, align?: string, width?: string}>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'nama_tim', 'name' => 'tims.nama_tim', 'label' => 'NAMA TIM', 'sortable' => true, 'searchable' => true, 'width' => 'w-44'],
            ['data' => 'lingkup', 'name' => 'tims.lingkup', 'label' => 'LINGKUP', 'sortable' => true, 'searchable' => true, 'width' => 'w-44'],
            ['data' => 'daltu_name', 'name' => 'daltu.name', 'label' => 'DALTU & DALNIS', 'sortable' => true, 'searchable' => true, 'width' => 'w-52'],
            ['data' => 'ketua_tim_name', 'name' => 'ketua_tim.name', 'label' => 'KETUA TIM', 'sortable' => true, 'searchable' => true, 'width' => 'w-48'],
            ['data' => 'auditors_names', 'name' => 'auditors_names', 'label' => 'ANGGOTA (AUDITOR)', 'sortable' => false, 'searchable' => true],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $plottings
     * @param  iterable<int, mixed>  $provinces
     * @param  array<string, mixed>  $usersByRole
     * @return array<string, mixed>
     */
    public function config(iterable $plottings, iterable $provinces, array $usersByRole): array
    {
        $authUser = auth()->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';

        $lingkups = match (true) {
            $isAdminNasional => ['Nasional'],
            $isAdminWilayah => ['Wilayah'],
            default => Tim::LINGKUPS,
        };

        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        return [
            'indexUrl' => route('master.tim.index'),
            'storeUrl' => route('master.tim.store'),
            'baseUrl' => url('/master/tim'),
            'authRole' => $authUser?->role,
            'authPlottingId' => $isAdminNasional ? $authUser?->plotting_id : null,
            'authProvinceCode' => $authProvinceCode,
            'lingkups' => $lingkups,
            'plottings' => $plottings,
            'provinces' => $provinces,
            'daltuOptions' => $usersByRole['daltu'] ?? [],
            'dalnisOptions' => $usersByRole['dalnis'] ?? [],
            'ketuaTimOptions' => $usersByRole['ketua_tim'] ?? [],
            'auditorCandidates' => $usersByRole['auditor_candidates'] ?? [],
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $filterLingkup = (string) $request->query('lingkup', '');
        $authUser = $request->user();

        $query = Tim::query()
            ->with(['auditors:id,name,role'])
            ->leftJoin('plottings', 'plottings.id', '=', 'tims.plotting_id')
            ->leftJoin('wilayah as p', 'p.kode_wilayah', '=', 'tims.province_code')
            ->leftJoin('users as daltu', 'daltu.id', '=', 'tims.daltu_id')
            ->leftJoin('users as dalnis', 'dalnis.id', '=', 'tims.dalnis_id')
            ->leftJoin('users as ketua_tim', 'ketua_tim.id', '=', 'tims.ketua_tim_id')
            ->leftJoin('users as ketua_tim_2', 'ketua_tim_2.id', '=', 'tims.ketua_tim_2_id')
            ->select([
                'tims.*',
                'plottings.plot as plot_name',
                'p.provinsi as province_name',
                'daltu.name as daltu_name',
                'dalnis.name as dalnis_name',
                'ketua_tim.name as ketua_tim_name',
                'ketua_tim_2.name as ketua_tim_2_name',
            ]);

        if ($authUser?->role === 'Admin Nasional' && $authUser->plotting_id) {
            $query->where('tims.lingkup', 'Nasional')
                ->where('tims.plotting_id', $authUser->plotting_id);
        } elseif ($authUser?->role === 'Admin Wilayah' && $authUser->kode_wilayah) {
            $authProvinceCode = substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2);
            $query->where('tims.lingkup', 'Wilayah')
                ->where('tims.province_code', $authProvinceCode);
        } elseif ($filterLingkup !== '') {
            $query->where('tims.lingkup', $filterLingkup);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('auditors_list', fn (Tim $tim): array => $tim->auditors->map(
                fn (User $auditor): array => [
                    'id' => (int) $auditor->id,
                    'name' => (string) $auditor->name,
                    'role' => (string) $auditor->role,
                ]
            )->values()->all())
            ->addColumn('auditor_ids', fn (Tim $tim): array => $tim->auditors->pluck('id')->map(
                fn (mixed $id): int => (int) $id
            )->values()->all())
            ->addColumn('actual_auditors_count', fn (Tim $tim): int => $tim->auditors->count())
            ->addColumn('needs_sync', fn (Tim $tim): bool => (int) $tim->jumlah_anggota !== $tim->auditors->count())
            ->addColumn('auditors_names', fn (Tim $tim): string => $tim->auditors->pluck('name')->implode(', '))
            ->filterColumn('daltu_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $builder) use ($keyword): void {
                    $builder->where('daltu.name', 'like', "%{$keyword}%")
                        ->orWhere('dalnis.name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('ketua_tim_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $builder) use ($keyword): void {
                    $builder->where('ketua_tim.name', 'like', "%{$keyword}%")
                        ->orWhere('ketua_tim_2.name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('auditors_names', function (Builder $query, string $keyword): void {
                $query->whereHas('auditors', function (Builder $builder) use ($keyword): void {
                    $builder->where('users.name', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }
}
