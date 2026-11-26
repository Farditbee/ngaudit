<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\PkaLangkahKerjaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\PkaLangkahKerjaRequest;
use App\Models\Pka;
use App\Models\PkaLangkahKerja;
use App\Models\Sprin;
use App\Models\TemplateLangkahKerja;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PkaLangkahKerjaController extends Controller
{
    public function index(Request $request, Pka $pka, PkaLangkahKerjaDataTable $dataTable): View|JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            abort(403, 'Anda tidak memiliki hak akses untuk PKA ini.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request, $pka);
        }

        $pka->load([
            'sprin.pengendaliMutu:id,name,username',
            'sprin.pengawasTim:id,name,username',
            'sprin.ketuaTim:id,name,username',
            'sprin.ketuaTim2:id,name,username',
            'sprin.tim.auditors:id,name,username,role',
        ]);

        $auditors = $this->resolveSprinAuditors($pka);

        $templates = TemplateLangkahKerja::query()
            ->visibleTo($request->user())
            ->withCount('details')
            ->orderBy('nama_template')
            ->get(['id', 'nama_template', 'lingkup', 'jumlah_langkah_kerja'])
            ->map(fn (TemplateLangkahKerja $t): array => [
                'id' => $t->id,
                'nama_template' => $t->nama_template,
                'lingkup' => $t->lingkup,
                'jumlah_langkah_kerja' => (int) ($t->details_count ?? $t->jumlah_langkah_kerja),
            ])
            ->values();

        $tableConfig = $dataTable->config($pka, $auditors, $templates);

        return view('audit.pka.langkah-kerja', [
            'pka' => $pka,
            'auditors' => $auditors,
            'templates' => $templates,
            'tableConfig' => $tableConfig,
        ]);
    }

    public function store(PkaLangkahKerjaRequest $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA ini.',
            ], 403);
        }

        $validated = $request->validated();

        $item = $pka->langkahKerjas()->create([
            'bidang' => $pka->bidang,
            'uraian' => $validated['uraian'],
            'dilaksanakan_rencana_id' => ! empty($validated['dilaksanakan_rencana_id']) ? (int) $validated['dilaksanakan_rencana_id'] : null,
            'dilaksanakan_realisasi_id' => ! empty($validated['dilaksanakan_realisasi_id']) ? (int) $validated['dilaksanakan_realisasi_id'] : null,
            'waktu_rencana' => isset($validated['waktu_rencana']) && $validated['waktu_rencana'] !== '' ? (int) $validated['waktu_rencana'] : null,
            'waktu_realisasi' => isset($validated['waktu_realisasi']) && $validated['waktu_realisasi'] !== '' ? (int) $validated['waktu_realisasi'] : null,
            'catatan' => $validated['catatan'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Langkah Kerja PKA berhasil ditambahkan.',
            'data' => $item,
        ], 201);
    }

    public function update(PkaLangkahKerjaRequest $request, Pka $pka, PkaLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka) || (int) $langkahKerja->pka_id !== (int) $pka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah langkah kerja ini.',
            ], 403);
        }

        $validated = $request->validated();

        $langkahKerja->update([
            'bidang' => $pka->bidang,
            'uraian' => $validated['uraian'],
            'dilaksanakan_rencana_id' => ! empty($validated['dilaksanakan_rencana_id']) ? (int) $validated['dilaksanakan_rencana_id'] : null,
            'dilaksanakan_realisasi_id' => ! empty($validated['dilaksanakan_realisasi_id']) ? (int) $validated['dilaksanakan_realisasi_id'] : null,
            'waktu_rencana' => isset($validated['waktu_rencana']) && $validated['waktu_rencana'] !== '' ? (int) $validated['waktu_rencana'] : null,
            'waktu_realisasi' => isset($validated['waktu_realisasi']) && $validated['waktu_realisasi'] !== '' ? (int) $validated['waktu_realisasi'] : null,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'message' => 'Langkah Kerja PKA berhasil diperbarui.',
            'data' => $langkahKerja,
        ]);
    }

    public function destroy(Request $request, Pka $pka, PkaLangkahKerja $langkahKerja): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka) || (int) $langkahKerja->pka_id !== (int) $pka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus langkah kerja ini.',
            ], 403);
        }

        $langkahKerja->delete();

        return response()->json([
            'message' => 'Langkah Kerja PKA berhasil dihapus.',
        ]);
    }

    public function bulkDestroy(Request $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus langkah kerja ini.',
            ], 403);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu data langkah kerja yang akan dihapus.',
            'ids.min' => 'Pilih minimal satu data langkah kerja yang akan dihapus.',
        ]);

        $deletedCount = DB::transaction(function () use ($pka, $validated): int {
            return PkaLangkahKerja::query()
                ->where('pka_id', $pka->id)
                ->whereIn('id', $validated['ids'])
                ->delete();
        });

        return response()->json([
            'message' => "{$deletedCount} langkah kerja PKA berhasil dihapus.",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function clear(Request $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengosongkan langkah kerja ini.',
            ], 403);
        }

        $deletedCount = DB::transaction(function () use ($pka): int {
            return PkaLangkahKerja::query()
                ->where('pka_id', $pka->id)
                ->delete();
        });

        return response()->json([
            'message' => 'Berhasil mengosongkan langkah kerja.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function applyTemplate(Request $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'template_langkah_kerja_id' => [
                'required',
                'integer',
                Rule::exists('template_langkah_kerjas', 'id')->whereNull('deleted_at'),
            ],
        ], [
            'template_langkah_kerja_id.required' => 'Silakan pilih Template Langkah Kerja terlebih dahulu.',
            'template_langkah_kerja_id.exists' => 'Template Langkah Kerja tidak ditemukan.',
        ]);

        $template = TemplateLangkahKerja::query()
            ->with(['details' => fn ($q) => $q->orderBy('id')])
            ->findOrFail($validated['template_langkah_kerja_id']);

        if ($template->details->isEmpty()) {
            return response()->json([
                'message' => "Template \"{$template->nama_template}\" belum memiliki rincian uraian langkah kerja.",
            ], 422);
        }

        $insertedCount = DB::transaction(function () use ($pka, $template, $request): int {
            $count = 0;
            foreach ($template->details as $detail) {
                $pka->langkahKerjas()->create([
                    'bidang' => $pka->bidang,
                    'uraian' => $detail->uraian,
                    'created_by' => $request->user()?->id,
                ]);
                $count++;
            }

            return $count;
        });

        return response()->json([
            'message' => "Berhasil menerapkan {$insertedCount} langkah kerja dari template \"{$template->nama_template}\".",
            'inserted_count' => $insertedCount,
        ], 201);
    }

    /**
     * @return list<array{id: int, name: string, label: string}>
     */
    protected function resolveSprinAuditors(Pka $pka): array
    {
        $sprin = $pka->sprin;
        if (! $sprin) {
            return [];
        }

        $users = collect();

        if ($sprin->ketuaTim) {
            $users->push($sprin->ketuaTim);
        }
        if ($sprin->ketuaTim2) {
            $users->push($sprin->ketuaTim2);
        }
        if ($sprin->tim && $sprin->tim->auditors) {
            foreach ($sprin->tim->auditors as $auditor) {
                $users->push($auditor);
            }
        }

        return $users
            ->unique('id')
            ->map(function (User $u): array {
                $selectName = $u->username ? "{$u->username} | {$u->name}" : (string) $u->name;

                return [
                    'id' => (int) $u->id,
                    'username' => (string) ($u->username ?? ''),
                    'name' => $selectName,
                    'label' => (string) $u->name,
                ];
            })
            ->values()
            ->all();
    }

    protected function canAccessPka(?User $user, Pka $pka): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        if ($pka->created_by && (int) $pka->created_by === (int) $user->id) {
            return true;
        }

        return $pka->sprin_id && Sprin::query()->whereKey($pka->sprin_id)->visibleTo($user)->exists();
    }
}
