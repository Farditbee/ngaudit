<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\KriteriaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\KriteriaRequest;
use App\Models\Kriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KriteriaController extends Controller
{
    public function index(Request $request, KriteriaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        return view('master.kriteria.index', [
            'tableConfig' => $dataTable->config(),
        ]);
    }

    public function store(KriteriaRequest $request): JsonResponse
    {
        $kriteria = Kriteria::query()->create([
            'nama' => trim((string) $request->validated('nama')),
            'is_opsnal' => $request->boolean('is_opsnal'),
            'is_logistik' => $request->boolean('is_logistik'),
            'is_sdm' => $request->boolean('is_sdm'),
            'is_garkeu' => $request->boolean('is_garkeu'),
        ]);

        return response()->json([
            'message' => 'Kriteria berhasil ditambahkan.',
            'data' => $kriteria,
        ], 201);
    }

    public function update(KriteriaRequest $request, Kriteria $kriteria): JsonResponse
    {
        $kriteria->update([
            'nama' => trim((string) $request->validated('nama')),
            'is_opsnal' => $request->boolean('is_opsnal'),
            'is_logistik' => $request->boolean('is_logistik'),
            'is_sdm' => $request->boolean('is_sdm'),
            'is_garkeu' => $request->boolean('is_garkeu'),
        ]);

        return response()->json([
            'message' => 'Kriteria berhasil diperbarui.',
            'data' => $kriteria,
        ]);
    }

    public function destroy(Kriteria $kriteria): JsonResponse
    {
        $kriteria->delete();

        return response()->json([
            'message' => 'Kriteria berhasil dihapus.',
        ]);
    }
}
