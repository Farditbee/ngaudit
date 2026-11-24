<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class WilayahController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->filled('options')) {
            return $this->options($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $this->datatable($request);
        }

        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $provinces = DB::table($prefix.'provinces')
            ->orderBy('code')
            ->get(['code', 'name']);

        return view('master.wilayah.index', compact('provinces'));
    }

    protected function options(Request $request): JsonResponse
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'cities' => DB::table($prefix.'cities')
                ->where('province_code', $parent)
                ->orderBy('code')
                ->get(['code', 'name']),
            'districts' => DB::table($prefix.'districts')
                ->where('city_code', $parent)
                ->orderBy('code')
                ->get(['code', 'name']),
            default => collect(),
        };

        return response()->json($items);
    }

    protected function datatable(Request $request): JsonResponse
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
                ->selectRaw("code, name as province_name, '' as city_name, '' as district_name, '' as village_name"),
            'province'
        );

        $cities = $applyFilters(
            DB::table($prefix.'cities as c')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw("c.code, p.name as province_name, c.name as city_name, '' as district_name, '' as village_name"),
            'city'
        );

        $districts = $applyFilters(
            DB::table($prefix.'districts as d')
                ->join($prefix.'cities as c', 'c.code', '=', 'd.city_code')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw("d.code, p.name as province_name, c.name as city_name, d.name as district_name, '' as village_name"),
            'district'
        );

        $villages = $applyFilters(
            DB::table($prefix.'villages as v')
                ->join($prefix.'districts as d', 'd.code', '=', 'v.district_code')
                ->join($prefix.'cities as c', 'c.code', '=', 'd.city_code')
                ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
                ->selectRaw('v.code, p.name as province_name, c.name as city_name, d.name as district_name, v.name as village_name'),
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
