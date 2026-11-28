<?php

namespace App\DataTables\Master;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WilayahDataTable
{
    /**
     * @return list<array{data: string, name: string, label: string, sortable: bool, searchable: bool, align?: string, width?: string, mono?: bool}>
     */
    public function columns(): array
    {
        return [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'label' => '#', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-16'],
            ['data' => 'code', 'name' => 'code', 'label' => 'KODE', 'sortable' => true, 'searchable' => true, 'mono' => true],
            ['data' => 'province_name', 'name' => 'province_name', 'label' => 'PROVINSI', 'sortable' => true, 'searchable' => true],
            ['data' => 'city_name', 'name' => 'city_name', 'label' => 'KABUPATEN/KOTA', 'sortable' => true, 'searchable' => true],
            ['data' => 'district_name', 'name' => 'district_name', 'label' => 'KECAMATAN', 'sortable' => true, 'searchable' => true],
            ['data' => 'village_name', 'name' => 'village_name', 'label' => 'DESA/KELURAHAN', 'sortable' => true, 'searchable' => true],
            ['data' => 'actions', 'name' => 'actions', 'label' => 'AKSI', 'sortable' => false, 'searchable' => false, 'align' => 'center', 'width' => 'w-28'],
        ];
    }

    /**
     * @param  iterable<int, object>  $provinces
     * @return array<string, mixed>
     */
    public function config(iterable $provinces): array
    {
        return [
            'indexUrl' => route('master.wilayah.index'),
            'storeUrl' => route('master.wilayah.store'),
            'baseUrl' => url('/master/wilayah'),
            'provinces' => $provinces,
            'columns' => $this->columns(),
        ];
    }

    public function toJson(Request $request): JsonResponse
    {
        $provinceCode = (string) $request->query('province_code', '');
        $cityCode = (string) $request->query('city_code', '');
        $districtCode = (string) $request->query('district_code', '');

        $targetTable = 'struktur_perangkat_daerah';

        $query = DB::table($targetTable)
            ->whereNull('deleted_at')
            ->selectRaw("
                kode_wilayah as code,
                kode_wilayah as raw_code,
                CASE
                    WHEN LENGTH(kode_wilayah) = 2 THEN 'province'
                    WHEN LENGTH(kode_wilayah) = 4 THEN 'city'
                    WHEN LENGTH(kode_wilayah) = 6 THEN 'district'
                    ELSE 'village'
                END as level,
                SUBSTRING(kode_wilayah, 1, 2) as province_code,
                CASE WHEN LENGTH(kode_wilayah) >= 4 THEN SUBSTRING(kode_wilayah, 1, 4) ELSE '' END as city_code,
                CASE WHEN LENGTH(kode_wilayah) >= 6 THEN SUBSTRING(kode_wilayah, 1, 6) ELSE '' END as district_code,
                COALESCE(provinsi, '') as province_name,
                COALESCE(kabupaten_kota, '') as city_name,
                COALESCE(kecamatan, '') as district_name,
                COALESCE(desa_kelurahan, '') as village_name
            ");

        if ($districtCode !== '') {
            $query->where('kode_wilayah', 'like', $districtCode.'%')
                ->whereRaw('LENGTH(kode_wilayah) >= 6');
        } elseif ($cityCode !== '') {
            $query->where('kode_wilayah', 'like', $cityCode.'%')
                ->whereRaw('LENGTH(kode_wilayah) >= 4');
        } elseif ($provinceCode !== '') {
            $query->where('kode_wilayah', 'like', $provinceCode.'%');
        }

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('code', fn (object $row): string => $this->formatCode((string) $row->code))
            ->filter(function (Builder $builder) use ($request): void {
                $keyword = trim((string) data_get($request->input('search'), 'value', ''));
                if ($keyword === '') {
                    return;
                }
                $cleanKeyword = str_replace('.', '', $keyword);
                $builder->where(function (Builder $sub) use ($keyword, $cleanKeyword): void {
                    $sub->where('kode_wilayah', 'like', "%{$cleanKeyword}%")
                        ->orWhere('provinsi', 'like', "%{$keyword}%")
                        ->orWhere('kabupaten_kota', 'like', "%{$keyword}%")
                        ->orWhere('kecamatan', 'like', "%{$keyword}%")
                        ->orWhere('desa_kelurahan', 'like', "%{$keyword}%");
                });
            })
            ->order(function (Builder $builder) use ($request): void {
                if (! $request->has('order')) {
                    $builder->orderByDesc('created_at')->orderByDesc('id');

                    return;
                }

                $orderColIndex = (int) data_get($request->input('order'), '0.column', 1);
                $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                $colMap = [
                    1 => 'kode_wilayah',
                    2 => 'provinsi',
                    3 => 'kabupaten_kota',
                    4 => 'kecamatan',
                    5 => 'desa_kelurahan',
                ];
                $builder->orderBy($colMap[$orderColIndex] ?? 'kode_wilayah', $orderDir);
            })
            ->toJson();
    }

    protected function formatCode(string $code): string
    {
        $length = strlen($code);

        if ($length === 10) {
            return substr($code, 0, 2).'.'.substr($code, 2, 2).'.'.substr($code, 4, 2).'.'.substr($code, 6, 4);
        }

        if ($length === 6) {
            return substr($code, 0, 2).'.'.substr($code, 2, 2).'.'.substr($code, 4, 2);
        }

        if ($length === 4) {
            return substr($code, 0, 2).'.'.substr($code, 2, 2);
        }

        return $code;
    }
}
