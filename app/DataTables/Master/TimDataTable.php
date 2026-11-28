<?php

namespace App\DataTables\Master;

use App\Models\StrukturPerangkatDaerah;
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
        $isAdminDinas = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true);
        $isAdminKecamatan = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true);

        $lingkups = match (true) {
            $isAdminDinas => ['Dinas'],
            $isAdminKecamatan => ['Kecamatan'],
            default => ['Dinas', 'Kecamatan'],
        };

        $dinasOptions = StrukturPerangkatDaerah::dinas()
            ->orderBy('nama_unit')
            ->get(['id', 'nama_unit'])
            ->map(fn ($u): array => ['id' => $u->id, 'code' => (string) $u->id, 'name' => $u->nama_unit])
            ->values()
            ->all();

        $kecamatanOptions = StrukturPerangkatDaerah::kecamatan()
            ->orderBy('nama_unit')
            ->get(['id', 'nama_unit'])
            ->map(fn ($u): array => ['id' => $u->id, 'code' => (string) $u->id, 'name' => $u->nama_unit])
            ->values()
            ->all();

        return [
            'indexUrl' => route('master.tim.index'),
            'storeUrl' => route('master.tim.store'),
            'baseUrl' => url('/master/tim'),
            'authRole' => $authUser?->role,
            'authSpdId' => $authUser?->struktur_perangkat_daerah_id ? (string) $authUser->struktur_perangkat_daerah_id : null,
            'lingkups' => $lingkups,
            'dinasOptions' => $dinasOptions,
            'kecamatanOptions' => $kecamatanOptions,
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
            ->leftJoin('struktur_perangkat_daerah as spd', 'spd.id', '=', 'tims.struktur_perangkat_daerah_id')
            ->leftJoin('users as daltu', 'daltu.id', '=', 'tims.daltu_id')
            ->leftJoin('users as dalnis', 'dalnis.id', '=', 'tims.dalnis_id')
            ->leftJoin('users as ketua_tim', 'ketua_tim.id', '=', 'tims.ketua_tim_id')
            ->leftJoin('users as ketua_tim_2', 'ketua_tim_2.id', '=', 'tims.ketua_tim_2_id')
            ->select([
                'tims.*',
                'spd.nama_unit as plot_name',
                'spd.nama_unit as province_name',
                'daltu.name as daltu_name',
                'dalnis.name as dalnis_name',
                'ketua_tim.name as ketua_tim_name',
                'ketua_tim_2.name as ketua_tim_2_name',
            ]);

        if (in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true)) {
            $query->whereIn('tims.lingkup', ['Nasional', 'Dinas']);
            if ($authUser->struktur_perangkat_daerah_id) {
                $unitIds = StrukturPerangkatDaerah::getDescendantIds((int) $authUser->struktur_perangkat_daerah_id);
                $query->whereIn('tims.struktur_perangkat_daerah_id', $unitIds);
            }
        } elseif (in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true)) {
            $query->whereIn('tims.lingkup', ['Wilayah', 'Kecamatan']);
            if ($authUser->struktur_perangkat_daerah_id) {
                $unitIds = StrukturPerangkatDaerah::getDescendantIds((int) $authUser->struktur_perangkat_daerah_id);
                $query->whereIn('tims.struktur_perangkat_daerah_id', $unitIds);
            } elseif ($authUser->kode_wilayah) {
                $cleanAuthWilayah = str_replace('.', '', (string) $authUser->kode_wilayah);
                $query->whereRaw("REPLACE(tims.province_code, '.', '') LIKE ?", [$cleanAuthWilayah.'%']);
            }
        } elseif ($filterLingkup !== '') {
            $query->where('tims.lingkup', $filterLingkup);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('tims.created_at')->orderByDesc('tims.id');
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
