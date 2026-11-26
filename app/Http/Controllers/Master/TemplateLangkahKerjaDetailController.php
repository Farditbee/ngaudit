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

        if ($user->plotting_id && ($user->role === 'Admin Nasional' || $user->level === 'Nasional')) {
            return $langkahKerja->lingkup === 'Nasional' && (int) $langkahKerja->plotting_id === (int) $user->plotting_id;
        }

        if ($user->kode_wilayah && ($user->role === 'Admin Wilayah' || $user->level !== 'Nasional')) {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

            return $langkahKerja->lingkup === 'Wilayah' && $langkahKerja->kode_wilayah === $provinceCode;
        }

        return true;
    }
}
