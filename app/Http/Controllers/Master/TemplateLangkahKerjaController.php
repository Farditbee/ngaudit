<?php

namespace App\Http\Controllers\Master;

use App\DataTables\Master\TemplateLangkahKerjaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\TemplateLangkahKerjaRequest;
use App\Models\Plotting;
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

        $authUser = $request->user();
        $isSuperAdmin = ! $authUser || $authUser->role === 'Super Admin';
        $isLockedNasional = ! $isSuperAdmin && ($authUser->role === 'Admin Nasional' || ($authUser->level === 'Nasional' && $authUser->plotting_id));
        $isLockedWilayah = ! $isSuperAdmin && ! $isLockedNasional && ($authUser->role === 'Admin Wilayah' || (bool) $authUser->kode_wilayah);

        $authProvinceCode = ($isLockedWilayah && $authUser?->kode_wilayah)
            ? substr(str_replace('.', '', (string) $authUser->kode_wilayah), 0, 2)
            : null;

        $plottingsQuery = Plotting::query()->orderBy('id');
        if ($isLockedNasional && $authUser?->plotting_id) {
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

        if ($isLockedWilayah && $authProvinceCode) {
            $provincesQuery->where('kode_wilayah', $authProvinceCode);
        }

        $provinces = $provincesQuery->get(['kode_wilayah as code', 'provinsi as name']);

        $tableConfig = $dataTable->config($plottings, $provinces);

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
            'plotting_id' => $langkahKerja->plotting_id ? (string) $langkahKerja->plotting_id : '',
            'plot_name' => $langkahKerja->plotName(),
            'kode_wilayah' => $langkahKerja->kode_wilayah ? (string) $langkahKerja->kode_wilayah : '',
            'province_name' => $langkahKerja->provinceName(),
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

        return [
            'nama_template' => $validated['nama_template'],
            'lingkup' => $lingkup,
            'plotting_id' => $lingkup === 'Nasional' ? (int) $validated['plotting_id'] : null,
            'kode_wilayah' => $lingkup === 'Wilayah' ? (string) $validated['kode_wilayah'] : null,
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
