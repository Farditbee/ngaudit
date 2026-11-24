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

        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $provinces = DB::table($prefix.'provinces')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

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

            return $plotting->load('provinces:id,code,name');
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
            'data' => $plotting->load('provinces:id,code,name'),
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
