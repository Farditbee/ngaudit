<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\WilayahDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\WilayahRequest;
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

        $tableConfig = $dataTable->config($provinces);

        return view('master.wilayah.index', compact('provinces', 'tableConfig'));
    }

    public function store(WilayahRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $level = $validated['level'];
        $now = now();

        match ($level) {
            'province' => DB::table($prefix.'provinces')->insert([
                'code' => $validated['code'],
                'name' => strtoupper(trim((string) $validated['province_name'])),
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'city' => DB::table($prefix.'cities')->insert([
                'code' => $validated['code'],
                'province_code' => $validated['province_code'],
                'name' => strtoupper(trim((string) $validated['city_name'])),
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'district' => DB::table($prefix.'districts')->insert([
                'code' => $validated['code'],
                'city_code' => $validated['city_code'],
                'name' => strtoupper(trim((string) $validated['district_name'])),
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'village' => DB::table($prefix.'villages')->insert([
                'code' => $validated['code'],
                'district_code' => $validated['district_code'],
                'name' => strtoupper(trim((string) $validated['village_name'])),
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        };

        return response()->json([
            'message' => 'Data wilayah berhasil ditambahkan.',
            'data' => [
                'level' => $level,
                'code' => $validated['code'],
            ],
        ], 201);
    }

    public function update(WilayahRequest $request, string $level, string $code): JsonResponse
    {
        $validated = $request->validated();
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $now = now();

        $updated = match ($level) {
            'province' => DB::table($prefix.'provinces')
                ->where('code', $code)
                ->update([
                    'code' => $validated['code'],
                    'name' => strtoupper(trim((string) $validated['province_name'])),
                    'updated_at' => $now,
                ]),
            'city' => DB::table($prefix.'cities')
                ->where('code', $code)
                ->update([
                    'code' => $validated['code'],
                    'province_code' => $validated['province_code'],
                    'name' => strtoupper(trim((string) $validated['city_name'])),
                    'updated_at' => $now,
                ]),
            'district' => DB::table($prefix.'districts')
                ->where('code', $code)
                ->update([
                    'code' => $validated['code'],
                    'city_code' => $validated['city_code'],
                    'name' => strtoupper(trim((string) $validated['district_name'])),
                    'updated_at' => $now,
                ]),
            'village' => DB::table($prefix.'villages')
                ->where('code', $code)
                ->update([
                    'code' => $validated['code'],
                    'district_code' => $validated['district_code'],
                    'name' => strtoupper(trim((string) $validated['village_name'])),
                    'updated_at' => $now,
                ]),
            default => 0,
        };

        if ($updated === 0) {
            return response()->json(['message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Data wilayah berhasil diperbarui.',
            'data' => [
                'level' => $level,
                'code' => $validated['code'],
            ],
        ]);
    }

    public function destroy(string $level, string $code): JsonResponse
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        $hasChildren = match ($level) {
            'province' => DB::table($prefix.'cities')->where('province_code', $code)->exists(),
            'city' => DB::table($prefix.'districts')->where('city_code', $code)->exists(),
            'district' => DB::table($prefix.'villages')->where('district_code', $code)->exists(),
            default => false,
        };

        if ($hasChildren) {
            return response()->json([
                'message' => 'Tidak dapat menghapus wilayah ini karena masih memiliki sub-wilayah di bawahnya.',
            ], 422);
        }

        $table = match ($level) {
            'province' => $prefix.'provinces',
            'city' => $prefix.'cities',
            'district' => $prefix.'districts',
            'village' => $prefix.'villages',
            default => null,
        };

        if ($table === null) {
            return response()->json(['message' => 'Tingkat wilayah tidak valid.'], 422);
        }

        DB::table($table)->where('code', $code)->delete();

        return response()->json([
            'message' => 'Data wilayah berhasil dihapus.',
        ]);
    }

    protected function options(Request $request): JsonResponse
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'provinces' => DB::table($prefix.'provinces')
                ->orderBy('code')
                ->get(['code', 'name']),
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
