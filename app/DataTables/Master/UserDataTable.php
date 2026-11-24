<?php

namespace App\DataTables\Master;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return [
            'indexUrl' => route('master.user.index'),
            'storeUrl' => route('master.user.store'),
            'baseUrl' => url('/master/user'),
            'roles' => User::ROLES,
            'levels' => User::LEVELS,
            'bidangs' => User::BIDANGS,
            'plottings' => $plottings,
            'provinces' => $provinces,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $filterRole = (string) $request->query('role', '');

        $query = User::query()
            ->leftJoin('plottings', 'plottings.id', '=', 'users.plotting_id')
            ->leftJoin($prefix.'provinces as p', 'p.code', '=', 'users.province_code')
            ->leftJoin($prefix.'cities as c', 'c.code', '=', 'users.city_code')
            ->leftJoin($prefix.'districts as d', 'd.code', '=', 'users.district_code')
            ->leftJoin($prefix.'villages as v', 'v.code', '=', 'users.village_code')
            ->select([
                'users.*',
                'plottings.plot as plot_name',
                'p.name as province_name',
                'c.name as city_name',
                'd.name as district_name',
                'v.name as village_name',
            ]);

        if ($filterRole !== '') {
            $query->where('users.role', $filterRole);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('ttd_url', fn (User $user): ?string => $user->ttd_path ? Storage::disk('public')->url($user->ttd_path) : null)
            ->toJson();
    }
}
