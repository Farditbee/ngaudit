<?php

namespace App\DataTables\Master;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';

        $roles = match (true) {
            $isAdminNasional => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Wilayah'])),
            $isAdminWilayah => array_values(array_diff(User::ROLES, ['Super Admin', 'Admin Nasional'])),
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
            ->leftJoin('plottings', 'plottings.id', '=', 'users.plotting_id')
            ->leftJoin('wilayah as w', 'w.kode_wilayah', '=', DB::raw($cleanCodeSql))
            ->select([
                'users.*',
                'plottings.plot as plot_name',
                DB::raw("CASE WHEN CHAR_LENGTH({$cleanCodeSql}) >= 2 THEN SUBSTRING({$cleanCodeSql}, 1, 2) ELSE NULL END as province_code"),
                'w.provinsi as province_name',
                DB::raw("CASE WHEN CHAR_LENGTH({$cleanCodeSql}) >= 4 THEN SUBSTRING({$cleanCodeSql}, 1, 4) ELSE NULL END as city_code"),
                'w.kabupaten_kota as city_name',
                DB::raw("CASE WHEN CHAR_LENGTH({$cleanCodeSql}) >= 6 THEN SUBSTRING({$cleanCodeSql}, 1, 6) ELSE NULL END as district_code"),
                'w.kecamatan as district_name',
                DB::raw("CASE WHEN CHAR_LENGTH({$cleanCodeSql}) = 10 THEN {$cleanCodeSql} ELSE NULL END as village_code"),
                'w.desa_kelurahan as village_name',
            ]);

        if ($filterRole !== '') {
            $query->where('users.role', $filterRole);
        }

        $authUser = $request->user();
        if ($authUser?->role === 'Admin Nasional' && $authUser->plotting_id) {
            $query->where('users.plotting_id', $authUser->plotting_id);
        } elseif ($authUser?->role === 'Admin Wilayah' && $authUser->kode_wilayah) {
            $cleanAuthWilayah = str_replace('.', '', (string) $authUser->kode_wilayah);
            $query->whereRaw("{$cleanCodeSql} LIKE ?", [$cleanAuthWilayah.'%']);
        }

        if (! $request->has('order')) {
            $query->orderByDesc('users.created_at')->orderByDesc('users.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('ttd_url', fn (User $user): ?string => $user->ttd_path ? Storage::disk('public')->url($user->ttd_path) : null)
            ->toJson();
    }
}
