<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplateLangkahKerjaDetailDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplateLangkahKerjaDetailRequest;
use App\Models\TemplateLangkahKerja;
use App\Models\TemplateLangkahKerjaDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TemplateLangkahKerjaDetailController extends Controller
{
    public function index(Request $request, TemplateLangkahKerja $langkahKerja, TemplateLangkahKerjaDetailDataTable $dataTable): View|JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja)) {
            abort(403, 'Anda tidak memiliki hak akses untuk template langkah kerja ini.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request, $langkahKerja);
        }

        $langkahKerja->refreshJumlahLangkahKerja();
        $tableConfig = $dataTable->config($langkahKerja);

        return view('master.template.langkah-kerja.detail', compact('langkahKerja', 'tableConfig'));
    }

    public function store(TemplateLangkahKerjaDetailRequest $request, TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk template ini.',
            ], 403);
        }

        $validated = $request->validated();

        $detail = $langkahKerja->details()->create([
            'uraian' => $validated['uraian'],
            'created_by' => $request->user()?->id,
        ]);

        $count = $langkahKerja->refreshJumlahLangkahKerja();

        return response()->json([
            'message' => 'Uraian langkah kerja berhasil ditambahkan.',
            'data' => $detail,
            'jumlah_langkah_kerja' => $count,
        ], 201);
    }

    public function update(TemplateLangkahKerjaDetailRequest $request, TemplateLangkahKerja $langkahKerja, TemplateLangkahKerjaDetail $detail): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja) || (int) $detail->template_langkah_kerja_id !== (int) $langkahKerja->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah uraian langkah kerja ini.',
            ], 403);
        }

        $validated = $request->validated();
        $detail->update([
            'uraian' => $validated['uraian'],
        ]);

        return response()->json([
            'message' => 'Uraian langkah kerja berhasil diperbarui.',
            'data' => $detail,
            'jumlah_langkah_kerja' => (int) $langkahKerja->jumlah_langkah_kerja,
        ]);
    }

    public function destroy(Request $request, TemplateLangkahKerja $langkahKerja, TemplateLangkahKerjaDetail $detail): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja) || (int) $detail->template_langkah_kerja_id !== (int) $langkahKerja->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus uraian langkah kerja ini.',
            ], 403);
        }

        $detail->delete();
        $count = $langkahKerja->refreshJumlahLangkahKerja();

        return response()->json([
            'message' => 'Uraian langkah kerja berhasil dihapus.',
            'jumlah_langkah_kerja' => $count,
        ]);
    }

    public function bulkDestroy(Request $request, TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus uraian langkah kerja ini.',
            ], 403);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu data langkah kerja yang akan dihapus.',
            'ids.min' => 'Pilih minimal satu data langkah kerja yang akan dihapus.',
        ]);

        $deletedCount = DB::transaction(function () use ($langkahKerja, $validated): int {
            return TemplateLangkahKerjaDetail::query()
                ->where('template_langkah_kerja_id', $langkahKerja->id)
                ->whereIn('id', $validated['ids'])
                ->delete();
        });

        $count = $langkahKerja->refreshJumlahLangkahKerja();

        return response()->json([
            'message' => "{$deletedCount} uraian langkah kerja berhasil dihapus.",
            'deleted_count' => $deletedCount,
            'jumlah_langkah_kerja' => $count,
        ]);
    }

    protected function canAccessTemplate(?User $user, TemplateLangkahKerja $langkahKerja): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        $isAdminDinas = in_array($user->role, ['Admin Nasional', 'Admin Dinas'], true) || ($user->level === 'Dinas');
        $isAdminKecamatan = in_array($user->role, ['Admin Wilayah', 'Admin Kecamatan'], true) || ($user->level === 'Kecamatan');

        if ($isAdminDinas) {
            if ($langkahKerja->lingkup !== 'Dinas') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $langkahKerja->struktur_perangkat_daerah_id) {
                return (int) $langkahKerja->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        if ($isAdminKecamatan) {
            if ($langkahKerja->lingkup !== 'Kecamatan') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $langkahKerja->struktur_perangkat_daerah_id) {
                return (int) $langkahKerja->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        return true;
    }
}
