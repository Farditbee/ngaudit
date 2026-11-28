<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplateKkaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplateKkaRequest;
use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateKkaController extends Controller
{
    public function index(Request $request, TemplateKkaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $tableConfig = $dataTable->config([], []);

        return view('master.template.kka.index', compact('tableConfig'));
    }

    public function store(TemplateKkaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $template = TemplateKka::query()->create($payload);

        return response()->json([
            'message' => 'Template KKA berhasil ditambahkan.',
            'data' => $template,
        ], 201);
    }

    public function show(TemplateKka $kka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk template ini.',
            ], 403);
        }

        return response()->json([
            'id' => $kka->id,
            'nama_template' => $kka->nama_template,
            'lingkup' => $kka->lingkup,
            'struktur_perangkat_daerah_id' => $kka->struktur_perangkat_daerah_id ? (string) $kka->struktur_perangkat_daerah_id : '',
            'plotting_id' => $kka->struktur_perangkat_daerah_id ? (string) $kka->struktur_perangkat_daerah_id : '',
            'plot_name' => $kka->plotName(),
            'kode_wilayah' => '',
            'province_name' => '',
            'tujuan' => $kka->tujuan ?? '',
            'langkah' => $kka->langkah ?? '',
            'hasil' => $kka->hasil ?? '',
            'created_at' => $kka->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ]);
    }

    public function update(TemplateKkaRequest $request, TemplateKka $kka): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah template ini.',
            ], 403);
        }

        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $kka->update($payload);

        return response()->json([
            'message' => 'Template KKA berhasil diperbarui.',
            'data' => $kka,
        ]);
    }

    public function destroy(TemplateKka $kka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus template ini.',
            ], 403);
        }

        $kka->delete();

        return response()->json([
            'message' => 'Template KKA berhasil dihapus.',
        ]);
    }

    public function duplicate(TemplateKka $kka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menduplikasi template ini.',
            ], 403);
        }

        $copy = $kka->replicate();
        $copy->nama_template = $this->generateDuplicateName($kka->nama_template);
        $copy->created_by = auth()->id();
        $copy->save();

        return response()->json([
            'message' => 'Template KKA berhasil diduplikasi.',
            'data' => $copy,
        ], 201);
    }

    protected function generateDuplicateName(string $name): string
    {
        $baseName = trim((string) preg_replace('/\s*\(Salinan(?:\s+\d+)?\)$/i', '', $name));
        $candidate = "{$baseName} (Salinan)";
        $counter = 2;

        while (TemplateKka::query()->where('nama_template', $candidate)->exists()) {
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
            'tujuan' => $validated['tujuan'] ?? null,
            'langkah' => $validated['langkah'] ?? null,
            'hasil' => $validated['hasil'] ?? null,
        ];
    }

    protected function canAccessTemplate(?User $user, TemplateKka $kka): bool
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
            if ($kka->lingkup !== 'Dinas') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $kka->struktur_perangkat_daerah_id) {
                return (int) $kka->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        if ($isAdminKecamatan) {
            if ($kka->lingkup !== 'Kecamatan') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $kka->struktur_perangkat_daerah_id) {
                return (int) $kka->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        return true;
    }
}
