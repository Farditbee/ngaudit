<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplatePkaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplatePkaRequest;
use App\Models\TemplatePka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplatePkaController extends Controller
{
    public function index(Request $request, TemplatePkaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $tableConfig = $dataTable->config([], []);

        return view('master.template.pka.index', compact('tableConfig'));
    }

    public function store(TemplatePkaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $template = TemplatePka::query()->create($payload);

        return response()->json([
            'message' => 'Template PKA berhasil ditambahkan.',
            'data' => $template,
        ], 201);
    }

    public function show(TemplatePka $pka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk template ini.',
            ], 403);
        }

        return response()->json([
            'id' => $pka->id,
            'nama_template' => $pka->nama_template,
            'lingkup' => $pka->lingkup,
            'struktur_perangkat_daerah_id' => $pka->struktur_perangkat_daerah_id ? (string) $pka->struktur_perangkat_daerah_id : '',
            'plotting_id' => $pka->struktur_perangkat_daerah_id ? (string) $pka->struktur_perangkat_daerah_id : '',
            'plot_name' => $pka->plotName(),
            'kode_wilayah' => '',
            'province_name' => '',
            'pendahuluan' => $pka->pendahuluan ?? '',
            'tujuan' => $pka->tujuan ?? '',
            'ruang_lingkup' => $pka->ruang_lingkup ?? '',
            'sasaran' => $pka->sasaran ?? '',
            'waktu_pelaksanaan' => $pka->waktu_pelaksanaan ?? '',
            'periode' => $pka->periode ?? '',
            'penutup' => $pka->penutup ?? '',
            'created_at' => $pka->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ]);
    }

    public function update(TemplatePkaRequest $request, TemplatePka $pka): JsonResponse
    {
        if (! $this->canAccessTemplate($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah template ini.',
            ], 403);
        }

        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        $pka->update($payload);

        return response()->json([
            'message' => 'Template PKA berhasil diperbarui.',
            'data' => $pka,
        ]);
    }

    public function destroy(TemplatePka $pka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus template ini.',
            ], 403);
        }

        $pka->delete();

        return response()->json([
            'message' => 'Template PKA berhasil dihapus.',
        ]);
    }

    public function duplicate(TemplatePka $pka): JsonResponse
    {
        if (! $this->canAccessTemplate(auth()->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menduplikasi template ini.',
            ], 403);
        }

        $copy = $pka->replicate();
        $copy->nama_template = $this->generateDuplicateName($pka->nama_template);
        $copy->created_by = auth()->id();
        $copy->save();

        return response()->json([
            'message' => 'Template PKA berhasil diduplikasi.',
            'data' => $copy,
        ], 201);
    }

    protected function generateDuplicateName(string $name): string
    {
        $baseName = trim((string) preg_replace('/\s*\(Salinan(?:\s+\d+)?\)$/i', '', $name));
        $candidate = "{$baseName} (Salinan)";
        $counter = 2;

        while (TemplatePka::query()->where('nama_template', $candidate)->exists()) {
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
            'pendahuluan' => $validated['pendahuluan'] ?? null,
            'tujuan' => $validated['tujuan'] ?? null,
            'ruang_lingkup' => $validated['ruang_lingkup'] ?? null,
            'sasaran' => $validated['sasaran'] ?? null,
            'waktu_pelaksanaan' => $validated['waktu_pelaksanaan'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'penutup' => $validated['penutup'] ?? null,
        ];
    }

    protected function canAccessTemplate(?User $user, TemplatePka $pka): bool
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
            if ($pka->lingkup !== 'Dinas') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $pka->struktur_perangkat_daerah_id) {
                return (int) $pka->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        if ($isAdminKecamatan) {
            if ($pka->lingkup !== 'Kecamatan') {
                return false;
            }
            if ($user->struktur_perangkat_daerah_id && $pka->struktur_perangkat_daerah_id) {
                return (int) $pka->struktur_perangkat_daerah_id === (int) $user->struktur_perangkat_daerah_id;
            }

            return true;
        }

        return true;
    }
}
