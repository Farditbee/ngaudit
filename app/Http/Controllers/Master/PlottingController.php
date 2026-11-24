<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\PlottingDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PlottingRequest;
use App\Models\Plotting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlottingController extends Controller
{
    public function index(Request $request, PlottingDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $provinces = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['id', 'kode_wilayah as code', 'provinsi as name']);

        $tableConfig = $dataTable->config($provinces);

        return view('master.plotting.index', compact('provinces', 'tableConfig'));
    }

    public function store(PlottingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $plotting = DB::transaction(function () use ($validated): Plotting {
            $plotting = Plotting::query()->create([
                'plot' => $validated['plot'],
            ]);

            $plotting->provinces()->sync($validated['provinsi_ids']);

            return $plotting->load('provinces:id,kode_wilayah,provinsi');
        });

        return response()->json([
            'message' => 'Data plotting berhasil ditambahkan.',
            'data' => $plotting,
        ], 201);
    }

    public function update(PlottingRequest $request, Plotting $plotting): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($plotting, $validated): void {
            $plotting->update([
                'plot' => $validated['plot'],
            ]);

            $plotting->provinces()->sync($validated['provinsi_ids']);
        });

        return response()->json([
            'message' => 'Data plotting berhasil diperbarui.',
            'data' => $plotting->load('provinces:id,kode_wilayah,provinsi'),
        ]);
    }

    public function destroy(Plotting $plotting): JsonResponse
    {
        $plotting->delete();

        return response()->json([
            'message' => 'Data plotting berhasil dihapus.',
        ]);
    }
}
