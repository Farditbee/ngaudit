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
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $provinceCode = (string) $request->query('province_code', '');
        $cityCode = (string) $request->query('city_code', '');
        $districtCode = (string) $request->query('district_code', '');

        $applyFilters = function (Builder $query, string $level) use ($provinceCode, $cityCode, $districtCode): Builder {
            if ($districtCode !== '') {
                return match ($level) {
                    'district' => $query->where('d.code', $districtCode),
                    'village' => $query->where('v.district_code', $districtCode),
                    default => $query->whereRaw('1 = 0'),
                };
            }

            if ($cityCode !== '') {
                return match ($level) {
                    'city' => $query->where('c.code', $cityCode),
                    'district' => $query->where('d.city_code', $cityCode),
                    'village' => $query->where('c.code', $cityCode),
                    default => $query->whereRaw('1 = 0'),
                };
            }

            if ($provinceCode !== '') {
                return match ($level) {
                    'province' => $query->where('code', $provinceCode),
                    'city' => $query->where('c.province_code', $provinceCode),
                    'district', 'village' => $query->where('p.code', $provinceCode),
                };
            }

            return $query;
        };

        $provinces = $applyFilters(
            DB::table($prefix.'provinces')
                ->selectRaw("code, code as raw_code, 'province' as level, code as province_code, '' as city_code, '' as district_code, name as province_name, '' as city_name, '' as district_name, '' as village_name"),
            'province'
        );

        $cities = $applyFilters(
            DB::table($prefix.'cities as c')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw("c.code, c.code as raw_code, 'city' as level, p.code as province_code, c.code as city_code, '' as district_code, p.name as province_name, c.name as city_name, '' as district_name, '' as village_name"),
            'city'
        );

        $districts = $applyFilters(
            DB::table($prefix.'districts as d')
                ->join($prefix.'cities as c', 'c.code', '=', 'd.city_code')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw("d.code, d.code as raw_code, 'district' as level, p.code as province_code, c.code as city_code, d.code as district_code, p.name as province_name, c.name as city_name, d.name as district_name, '' as village_name"),
            'district'
        );

        $villages = $applyFilters(
            DB::table($prefix.'villages as v')
                ->join($prefix.'districts as d', 'd.code', '=', 'v.district_code')
                ->join($prefix.'cities as c', 'c.code', '=', 'd.city_code')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw("v.code, v.code as raw_code, 'village' as level, p.code as province_code, c.code as city_code, d.code as district_code, p.name as province_name, c.name as city_name, d.name as district_name, v.name as village_name"),
            'village'
        );

        $union = $provinces
            ->unionAll($cities)
            ->unionAll($districts)
            ->unionAll($villages);

        $query = DB::query()->fromSub($union, 'wilayah');

        $dataTable = DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('code', fn (object $row): string => $this->formatCode((string) $row->code));

        if ($provinceCode === '' && $cityCode === '' && $districtCode === '') {
            $totalCount = (int) DB::table($prefix.'provinces')->count()
                + (int) DB::table($prefix.'cities')->count()
                + (int) DB::table($prefix.'districts')->count()
                + (int) DB::table($prefix.'villages')->count();

            $dataTable->setTotalRecords($totalCount);
        }

        return $dataTable->toJson();
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
