<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplateLangkahKerjaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplateLangkahKerjaRequest;
use App\Models\TemplateLangkahKerja;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TemplateLangkahKerjaController extends Controller
{
    public function index(Request $request, TemplateLangkahKerjaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $tableConfig = $dataTable->config([], []);

        return view('master.template.langkah-kerja.index', compact('tableConfig'));
    }

    public function store(TemplateLangkahKerjaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $template = TemplateLangkahKerja::query()->create($payload);

        return response()->json([
            'message' => 'Template Langkah Kerja berhasil ditambahkan.',
            'data' => $template,
        ], 201);
    }

    public function show(TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk template ini.',
            ], 403);
        }

        return response()->json([
            'id' => $langkahKerja->id,
            'nama_template' => $langkahKerja->nama_template,
            'lingkup' => $langkahKerja->lingkup,
            'struktur_perangkat_daerah_id' => $langkahKerja->struktur_perangkat_daerah_id ? (string) $langkahKerja->struktur_perangkat_daerah_id : '',
            'plotting_id' => $langkahKerja->struktur_perangkat_daerah_id ? (string) $langkahKerja->struktur_perangkat_daerah_id : '',
            'plot_name' => $langkahKerja->plotName(),
            'kode_wilayah' => '',
            'province_name' => '',
            'jumlah_langkah_kerja' => (int) $langkahKerja->jumlah_langkah_kerja,
            'created_at' => $langkahKerja->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ]);
    }

    public function update(TemplateLangkahKerjaRequest $request, TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah template ini.',
            ], 403);
        }

        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $langkahKerja->update($payload);

        return response()->json([
            'message' => 'Template Langkah Kerja berhasil diperbarui.',
            'data' => $langkahKerja,
        ]);
    }

    public function destroy(TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus template ini.',
            ], 403);
        }

        $langkahKerja->delete();

        return response()->json([
            'message' => 'Template Langkah Kerja berhasil dihapus.',
        ]);
    }

    public function duplicate(TemplateLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $langkahKerja)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menduplikasi template ini.',
            ], 403);
        }

        $copy = DB::transaction(function () use ($langkahKerja): TemplateLangkahKerja {
            $copy = $langkahKerja->replicate();
            $copy->nama_template = $this->generateDuplicateName($langkahKerja->nama_template);
            $copy->created_by = auth()->id();
            $copy->save();

            $details = $langkahKerja->details()->orderBy('id')->get();
            foreach ($details as $detail) {
                $copy->details()->create([
                    'uraian' => $detail->uraian,
                    'created_by' => auth()->id(),
                ]);
            }

            $copy->refreshJumlahLangkahKerja();

            return $copy;
        });

        return response()->json([
            'message' => 'Template Langkah Kerja berhasil diduplikasi.',
            'data' => $copy,
        ], 201);
    }

    protected function generateDuplicateName(string $name): string
    {
        $baseName = trim((string) preg_replace('/\s*\(Salinan(?:\s+\d+)?\)$/i', '', $name));
        $candidate = "{$baseName} (Salinan)";
        $counter = 2;

        while (TemplateLangkahKerja::query()->where('nama_template', $candidate)->exists()) {
            $candidate = "{$baseName} (Salinan {$counter})";
            $counter++;
        }

        return $candidate;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildPayload(array $validated): array
    {
        $lingkup = $validated['lingkup'];
        $spdId = $validated['struktur_perangkat_daerah_id'] ?? $validated['plotting_id'] ?? null;

        return [
            'nama_template' => $validated['nama_template'],
            'lingkup' => $lingkup,
            'struktur_perangkat_daerah_id' => $spdId ? (int) $spdId : null,
            'kode_wilayah' => null,
        ];
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
