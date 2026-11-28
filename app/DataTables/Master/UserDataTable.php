<?php

namespace App\DataTables\Master;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserDataTable
{
    /**
     * @return list<array{data: string, name: string, label: string, sortable: bool, searchable: bool, align?: string, width?: string}>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-12'],
            ['data' => 'name', 'name' => 'users.name', 'label' => 'NAMA', 'sortable' => true, 'searchable' => true, 'width' => 'w-44'],
            ['data' => 'jabatan', 'name' => 'users.jabatan', 'label' => 'JABATAN', 'sortable' => true, 'searchable' => true, 'width' => 'w-44'],
            ['data' => 'username', 'name' => 'users.username', 'label' => 'USERNAME', 'sortable' => true, 'searchable' => true],
            ['data' => 'email', 'name' => 'users.email', 'label' => 'EMAIL', 'sortable' => true, 'searchable' => true],
            ['data' => 'role', 'name' => 'users.role', 'label' => 'PERAN', 'sortable' => true, 'searchable' => true],
            ['data' => 'bidang', 'name' => 'users.bidang', 'label' => 'BIDANG', 'sortable' => true, 'searchable' => true],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
        ];
    }

    /**
     * @param  iterable<int, mixed>  $plottings
     * @param  iterable<int, mixed>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $plottings, iterable $provinces): array
    {
        $authUser = auth()->user();
        $isAdminNasional = in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true);
        $isAdminWilayah = in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true);

        $roles = match (true) {
            $isAdminNasional => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Kecamatan'])),
            $isAdminWilayah => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Dinas'])),
            default => User::ROLES,
        };

        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $canImpersonate = $authUser?->role === 'Super Admin' && ! session()->has('impersonator_id');

        return [
            'indexUrl' => route('master.user.index'),
            'storeUrl' => route('master.user.store'),
            'baseUrl' => url('/master/user'),
            'authRole' => $authUser?->role,
            'authPlottingId' => $isAdminNasional ? $authUser?->plotting_id : null,
            'authProvinceCode' => $authProvinceCode,
            'canImpersonate' => $canImpersonate,
            'roles' => $roles,
            'levels' => User::LEVELS,
            'bidangs' => User::BIDANGS,
            'plottings' => $plottings,
            'provinces' => $provinces,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $filterRole = (string) $request->query('role', '');
        $cleanCodeSql = "REPLACE(users.kode_wilayah, '.', '')";

        $query = User::query()
            ->leftJoin('struktur_perangkat_daerah as spd', function ($join): void {
                $join->on('spd.id', '=', 'users.struktur_perangkat_daerah_id')
                    ->orOn('spd.kode_wilayah', '=', 'users.kode_wilayah');
            })
            ->leftJoin('struktur_perangkat_daerah as spd_parent', 'spd_parent.id', '=', 'spd.parent_id')
            ->select([
                'users.*',
                'spd.nama_unit as perangkat_daerah_name',
                'spd.nama_unit as plot_name',
                'spd.level as spd_level',
                'spd.jenis_unit as spd_jenis_unit',
                'spd_parent.nama_unit as spd_parent_name',
                'spd.provinsi as province_name',
                'spd.kabupaten_kota as city_name',
                'spd.kecamatan as district_name',
                'spd.desa_kelurahan as village_name',
                'spd.pimpinan',
            ]);

        if ($filterRole !== '') {
            $query->where('users.role', $filterRole);
        }

        $authUser = $request->user();
        if (in_array($authUser?->role, ['Admin Nasional', 'Admin Dinas'], true) && $authUser->plotting_id) {
            $query->where('users.struktur_perangkat_daerah_id', $authUser->plotting_id);
        } elseif (in_array($authUser?->role, ['Admin Wilayah', 'Admin Kecamatan'], true) && $authUser->kode_wilayah) {
            $cleanAuthWilayah = str_replace('.', '', (string) $authUser->kode_wilayah);
            $query->whereRaw("{$cleanCodeSql} LIKE ?", [$cleanAuthWilayah.'%']);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('users.created_at')->orderByDesc('users.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('unit_parent', fn (User $user): ?string => $this->unitParent($user))
            ->addColumn('unit_sub', fn (User $user): ?string => $this->unitSub($user))
            ->addColumn('ttd_url', fn (User $user): ?string => $user->ttd_path ? asset('storage/'.$user->ttd_path) : null)
            ->filterColumn('perangkat_daerah_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $q) use ($keyword): void {
                    $q->where('spd.nama_unit', 'like', "%{$keyword}%")
                        ->orWhere('spd_parent.nama_unit', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }

    protected function unitParent(User $user): ?string
    {
        if (! empty($user->spd_parent_name)) {
            $level = isset($user->spd_level) ? (int) $user->spd_level : null;
            if ($level !== null && $level > 1) {
                return (string) $user->spd_parent_name;
            }

            if (! in_array($user->spd_jenis_unit, ['Kecamatan', 'Dinas', 'Badan', 'Inspektorat', 'Sekretariat', 'Sekretariat Daerah', 'Sekretariat DPRD'], true)) {
                return (string) $user->spd_parent_name;
            }
        }

        return null;
    }

    protected function unitSub(User $user): ?string
    {
        if (! $this->unitParent($user)) {
            return null;
        }

        $rawName = (string) ($user->perangkat_daerah_name ?? $user->plot_name ?? '');
        if ($rawName === '') {
            return null;
        }

        return preg_replace('/\s*-\s*Kec\.?\s+.*$/i', '', $rawName);
    }
}
