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

        $provinces = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', 'provinsi as name']);

        $tableConfig = $dataTable->config($provinces);

        return view('master.wilayah.index', compact('provinces', 'tableConfig'));
    }

    public function store(WilayahRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $level = $validated['level'];
        $now = now();
        $createdBy = auth()->id();

        match ($level) {
            'province' => DB::table('wilayah')->insert([
                'kode_wilayah' => $validated['code'],
                'provinsi' => strtoupper(trim((string) $validated['province_name'])),
                'kabupaten_kota' => null,
                'kecamatan' => null,
                'desa_kelurahan' => null,
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'city' => (function () use ($validated, $createdBy, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['province_code'])->first();
                DB::table('wilayah')->insert([
                    'kode_wilayah' => $validated['code'],
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => strtoupper(trim((string) $validated['city_name'])),
                    'kecamatan' => null,
                    'desa_kelurahan' => null,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            })(),
            'district' => (function () use ($validated, $createdBy, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['city_code'])->first();
                DB::table('wilayah')->insert([
                    'kode_wilayah' => $validated['code'],
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => $parent->kabupaten_kota ?? '',
                    'kecamatan' => strtoupper(trim((string) $validated['district_name'])),
                    'desa_kelurahan' => null,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            })(),
            'village' => (function () use ($validated, $createdBy, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['district_code'])->first();
                DB::table('wilayah')->insert([
                    'kode_wilayah' => $validated['code'],
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => $parent->kabupaten_kota ?? '',
                    'kecamatan' => $parent->kecamatan ?? '',
                    'desa_kelurahan' => strtoupper(trim((string) $validated['village_name'])),
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            })(),
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
        $now = now();
        $existing = DB::table('wilayah')->where('kode_wilayah', $code)->first();

        if (! $existing) {
            return response()->json(['message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        $newCode = (string) $validated['code'];

        match ($level) {
            'province' => (function () use ($code, $newCode, $validated, $now): void {
                $provName = strtoupper(trim((string) $validated['province_name']));
                DB::table('wilayah')->where('kode_wilayah', $code)->update([
                    'kode_wilayah' => $newCode,
                    'provinsi' => $provName,
                    'updated_at' => $now,
                ]);
                DB::table('wilayah')
                    ->where('kode_wilayah', 'like', $code.'%')
                    ->where('kode_wilayah', '!=', $newCode)
                    ->update([
                        'provinsi' => $provName,
                        'updated_at' => $now,
                    ]);
            })(),
            'city' => (function () use ($code, $newCode, $validated, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['province_code'])->first();
                $cityName = strtoupper(trim((string) $validated['city_name']));
                DB::table('wilayah')->where('kode_wilayah', $code)->update([
                    'kode_wilayah' => $newCode,
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => $cityName,
                    'updated_at' => $now,
                ]);
                DB::table('wilayah')
                    ->where('kode_wilayah', 'like', $code.'%')
                    ->where('kode_wilayah', '!=', $newCode)
                    ->update([
                        'provinsi' => $parent->provinsi ?? '',
                        'kabupaten_kota' => $cityName,
                        'updated_at' => $now,
                    ]);
            })(),
            'district' => (function () use ($code, $newCode, $validated, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['city_code'])->first();
                $distName = strtoupper(trim((string) $validated['district_name']));
                DB::table('wilayah')->where('kode_wilayah', $code)->update([
                    'kode_wilayah' => $newCode,
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => $parent->kabupaten_kota ?? '',
                    'kecamatan' => $distName,
                    'updated_at' => $now,
                ]);
                DB::table('wilayah')
                    ->where('kode_wilayah', 'like', $code.'%')
                    ->where('kode_wilayah', '!=', $newCode)
                    ->update([
                        'provinsi' => $parent->provinsi ?? '',
                        'kabupaten_kota' => $parent->kabupaten_kota ?? '',
                        'kecamatan' => $distName,
                        'updated_at' => $now,
                    ]);
            })(),
            'village' => (function () use ($code, $newCode, $validated, $now): void {
                $parent = DB::table('wilayah')->where('kode_wilayah', $validated['district_code'])->first();
                DB::table('wilayah')->where('kode_wilayah', $code)->update([
                    'kode_wilayah' => $newCode,
                    'provinsi' => $parent->provinsi ?? '',
                    'kabupaten_kota' => $parent->kabupaten_kota ?? '',
                    'kecamatan' => $parent->kecamatan ?? '',
                    'desa_kelurahan' => strtoupper(trim((string) $validated['village_name'])),
                    'updated_at' => $now,
                ]);
            })(),
            default => null,
        };

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
        $hasChildren = DB::table('wilayah')
            ->where('kode_wilayah', 'like', $code.'%')
            ->where('kode_wilayah', '!=', $code)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasChildren) {
            return response()->json([
                'message' => 'Tidak dapat menghapus wilayah ini karena masih memiliki sub-wilayah di bawahnya.',
            ], 422);
        }

        if (! in_array($level, ['province', 'city', 'district', 'village'], true)) {
            return response()->json(['message' => 'Tingkat wilayah tidak valid.'], 422);
        }

        DB::table('wilayah')->where('kode_wilayah', $code)->delete();

        return response()->json([
            'message' => 'Data wilayah berhasil dihapus.',
        ]);
    }

    protected function options(Request $request): JsonResponse
    {
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'provinces' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'provinsi as name']),
            'cities' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 4')
                ->where('kode_wilayah', 'like', $parent.'%')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'kabupaten_kota as name']),
            'districts' => DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 6')
                ->where('kode_wilayah', 'like', $parent.'%')
                ->whereNull('deleted_at')
                ->orderBy('kode_wilayah')
                ->get(['kode_wilayah as code', 'kecamatan as name']),
            default => collect(),
        };

        return response()->json($items);
    }
}
