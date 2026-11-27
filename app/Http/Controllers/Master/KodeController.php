<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\KodeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\KodeRequest;
use App\Models\Kode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KodeController extends Controller
{
    public function index(Request $request, string $jenis, KodeDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request, $jenis);
        }

        return view('master.kode.index', [
            'jenis' => $jenis,
            'isSub' => false,
            'requiresTemuan' => Kode::requiresTemuan($jenis),
            'title' => 'Kode '.ucfirst($jenis),
            'tableConfig' => $dataTable->config($jenis),
        ]);
    }

    public function store(KodeRequest $request, string $jenis): JsonResponse
    {
        $validated = $request->validated();

        $kode = Kode::query()->create([
            'jenis' => $jenis,
            'kode_temuan_id' => Kode::requiresTemuan($jenis) ? $validated['kode_temuan_id'] : null,
            'kode' => trim($validated['kode']),
            'nama' => trim($validated['nama']),
        ]);

        return response()->json([
            'message' => 'Kode '.$jenis.' berhasil ditambahkan.',
            'data' => $kode,
        ], 201);
    }

    public function update(KodeRequest $request, string $jenis, Kode $kode): JsonResponse
    {
        abort_unless($kode->jenis === $jenis, 404);

        $validated = $request->validated();

        $kode->update([
            'kode_temuan_id' => Kode::requiresTemuan($jenis) ? $validated['kode_temuan_id'] : null,
            'kode' => trim($validated['kode']),
            'nama' => trim($validated['nama']),
        ]);

        return response()->json([
            'message' => 'Kode '.$jenis.' berhasil diperbarui.',
            'data' => $kode,
        ]);
    }

    public function destroy(string $jenis, Kode $kode): JsonResponse
    {
        abort_unless($kode->jenis === $jenis, 404);

        DB::transaction(function () use ($kode): void {
            if ($kode->jenis === Kode::JENIS_TEMUAN) {
                $dependentIds = $kode->dependentKodes()->pluck('id');
                if ($dependentIds->isNotEmpty()) {
                    DB::table('sub_kodes')->whereIn('kode_id', $dependentIds)->update(['deleted_at' => now()]);
                    $kode->dependentKodes()->delete();
                }
            }

            $kode->subKodes()->delete();
            $kode->delete();
        });

        return response()->json([
            'message' => 'Kode '.$jenis.' beserta sub kodenya berhasil dihapus.',
        ]);
    }
}
