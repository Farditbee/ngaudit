<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\SubKodeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SubKodeRequest;
use App\Models\SubKode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubKodeController extends Controller
{
    public function index(Request $request, string $jenis, SubKodeDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request, $jenis);
        }

        return view('master.kode.index', [
            'jenis' => $jenis,
            'isSub' => true,
            'requiresTemuan' => false,
            'title' => 'Sub Kode '.ucfirst($jenis),
            'tableConfig' => $dataTable->config($jenis),
        ]);
    }

    public function store(SubKodeRequest $request, string $jenis): JsonResponse
    {
        $validated = $request->validated();

        $subKode = SubKode::query()->create([
            'kode_id' => $validated['kode_id'],
            'kode' => trim($validated['kode']),
            'nama' => trim($validated['nama']),
        ]);

        return response()->json([
            'message' => 'Sub kode '.$jenis.' berhasil ditambahkan.',
            'data' => $subKode,
        ], 201);
    }

    public function update(SubKodeRequest $request, string $jenis, SubKode $subKode): JsonResponse
    {
        abort_unless($subKode->parent?->jenis === $jenis, 404);

        $validated = $request->validated();

        $subKode->update([
            'kode_id' => $validated['kode_id'],
            'kode' => trim($validated['kode']),
            'nama' => trim($validated['nama']),
        ]);

        return response()->json([
            'message' => 'Sub kode '.$jenis.' berhasil diperbarui.',
            'data' => $subKode,
        ]);
    }

    public function destroy(string $jenis, SubKode $subKode): JsonResponse
    {
        abort_unless($subKode->parent?->jenis === $jenis, 404);

        $subKode->delete();

        return response()->json([
            'message' => 'Sub kode '.$jenis.' berhasil dihapus.',
        ]);
    }
}
