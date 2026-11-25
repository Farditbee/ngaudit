<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplatePkaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplatePkaRequest;
use App\Models\Plotting;
use App\Models\TemplatePka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TemplatePkaController extends Controller
{
    public function index(Request $request, TemplatePkaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $authUser = $request->user();
        $isAdminNasional = $authUser?->role === 'Admin Nasional';
        $isAdminWilayah = $authUser?->role === 'Admin Wilayah';
        $authProvinceCode = ($isAdminWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $plottingsQuery = Plotting::query()->orderBy('id');
        if ($isAdminNasional && $authUser?->plotting_id) {
            $plottingsQuery->whereKey($authUser->plotting_id);
        }

        $plottings = $plottingsQuery
            ->get(['id', 'plot'])
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
            ])
            ->values();

        $provincesQuery = DB::table('wilayah')
            ->whereRaw('LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah');

        if ($isAdminWilayah && $authProvinceCode) {
            $provincesQuery->where('kode_wilayah', $authProvinceCode);
        }

        $provinces = $provincesQuery->get(['kode_wilayah as code', 'provinsi as name']);

        $tableConfig = $dataTable->config($plottings, $provinces);

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
            'plotting_id' => $pka->plotting_id ? (string) $pka->plotting_id : '',
            'plot_name' => $pka->plotName(),
            'kode_wilayah' => $pka->kode_wilayah ? (string) $pka->kode_wilayah : '',
            'province_name' => $pka->provinceName(),
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

        return [
            'nama_template' => $validated['nama_template'],
            'lingkup' => $lingkup,
            'plotting_id' => $lingkup === 'Nasional' ? (int) $validated['plotting_id'] : null,
            'kode_wilayah' => $lingkup === 'Wilayah' ? (string) $validated['kode_wilayah'] : null,
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

        if ($user->role === 'Admin Nasional') {
            return $user->plotting_id && (int) $pka->plotting_id === (int) $user->plotting_id;
        }

        if ($user->role === 'Admin Wilayah') {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

            return $pka->lingkup === 'Wilayah' && $pka->kode_wilayah === $provinceCode;
        }

        return true;
    }
}
