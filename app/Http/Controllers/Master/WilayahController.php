<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\WilayahDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WilayahController extends Controller
{
    public function index(Request $request, WilayahDataTable $dataTable): View|JsonResponse
    {
        if ($request->filled('options')) {
            return $this->options($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
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
}
