<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\DataTables\Pelaksanaan\KkaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pelaksanaan\KkaRequest;
use App\Models\Kka;
use App\Models\KkaReview;
use App\Models\Pka;
use App\Models\Plotting;
use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KkaController extends Controller
{
    public function index(Request $request, KkaDataTable $dataTable): View|JsonResponse
    {
        if ($request->filled('options')) {
            return $this->options($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $authUser = $request->user();

        $formatUser = fn (?User $u): ?string => $u
            ? ($u->username ? "{$u->username} | {$u->name}" : (string) $u->name)
            : null;

        $pkas = Pka::query()
            ->visibleTo($authUser)
            ->where('pkas.status', 'Final')
            ->with([
                'sprin:id,no_sprin,tahap,sprin_tipe',
                'dibuatOleh:id,name,username',
                'direviuOleh:id,name,username',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Pka $pka): array => [
                'id' => $pka->id,
                'code' => (string) $pka->id,
                'name' => $pka->nomor_pka.' ('.($pka->sprin?->no_sprin ?? '-').')',
                'nomor_pka' => $pka->nomor_pka,
                'no_sprin' => $pka->sprin?->no_sprin ?? '-',
                'tahap' => $pka->sprin?->tahap,
                'bidang' => $pka->bidang,
                'jenis_audit' => $pka->jenis_audit,
                'dibuat_oleh_name' => $formatUser($pka->dibuatOleh),
                'direviu_oleh_name' => $formatUser($pka->direviuOleh),
            ])
            ->values();

        $templates = TemplateKka::query()
            ->visibleTo($authUser)
            ->orderBy('nama_template')
            ->get(['id', 'nama_template', 'lingkup', 'tujuan', 'langkah', 'hasil']);

        $plottings = Plotting::query()
            ->orderBy('id')
            ->get(['id', 'plot'])
            ->map(fn (Plotting $plotting): array => [
                'id' => $plotting->id,
                'code' => (string) $plotting->id,
                'name' => $plotting->plot,
            ])
            ->values();

        $provinces = DB::table('wilayah')
            ->whereRaw('LENGTH(kode_wilayah) = 2')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', 'provinsi as name']);

        return view('pelaksanaan.kka.index', [
            'dataTableConfig' => $dataTable->config($pkas, $templates, $plottings, $provinces),
            'columns' => $dataTable->columns(),
        ]);
    }

    public function store(KkaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! $this->canAccessPkaId($request->user(), (int) $validated['pka_id'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA yang dipilih.',
            ], 403);
        }

        $payload = $this->buildPayload($validated);
        $payload['created_by'] = $request->user()?->id;

        $kka = Kka::query()->create($payload);

        return response()->json([
            'message' => 'Kertas Kerja Audit (KKA) berhasil ditambahkan.',
            'data' => $kka,
        ], 201);
    }

    public function show(Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka(auth()->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk KKA ini.',
            ], 403);
        }

        $kka->load(['pka.sprin:id,no_sprin,tahap,sprin_tipe', 'templateKka:id,nama_template']);

        return response()->json([
            'id' => $kka->id,
            'nomor_kka' => $kka->nomor_kka,
            'pka_id' => $kka->pka_id ? (string) $kka->pka_id : '',
            'tanggal_dibuat' => $kka->tanggal_dibuat?->format('Y-m-d') ?? '',
            'tanggal_direviu' => $kka->tanggal_direviu?->format('Y-m-d') ?? '',
            'tanggal_kka' => $kka->tanggal_kka?->format('Y-m-d') ?? '',
            'tempat' => $kka->tempat,
            'auditee_level' => $kka->auditee_level,
            'auditee_plotting_id' => $kka->auditee_plotting_id ? (string) $kka->auditee_plotting_id : '',
            ...$kka->auditeeRegionCodes(),
            'template_kka_id' => $kka->template_kka_id ? (string) $kka->template_kka_id : '',
            'tujuan' => $kka->tujuan ?? '',
            'langkah' => $kka->langkah ?? '',
            'hasil' => $kka->hasil ?? '',
            'status' => $kka->status ?? 'Draft',
        ]);
    }

    public function update(KkaRequest $request, Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah KKA ini.',
            ], 403);
        }

        $validated = $request->validated();

        if (! $this->canAccessPkaId($request->user(), (int) $validated['pka_id'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA yang dipilih.',
            ], 403);
        }

        $kka->update($this->buildPayload($validated));

        return response()->json([
            'message' => 'Kertas Kerja Audit (KKA) berhasil diperbarui.',
            'data' => $kka,
        ]);
    }

    public function destroy(Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka(auth()->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus KKA ini.',
            ], 403);
        }

        $kka->delete();

        return response()->json([
            'message' => 'Kertas Kerja Audit (KKA) berhasil dihapus.',
        ]);
    }

    public function duplicate(Request $request, Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menduplikasi KKA ini.',
            ], 403);
        }

        $copy = $kka->replicate();
        $copy->nomor_kka = $request->filled('nomor_kka')
            ? (string) $request->input('nomor_kka')
            : $this->generateDuplicateNomorKka($kka->nomor_kka);
        $copy->status = 'Draft';
        $copy->approved_by_katim_date = null;
        $copy->approved_by_dalnis_date = null;
        $copy->approved_by_daltu_date = null;
        $copy->created_by = $request->user()?->id;
        $copy->save();

        return response()->json([
            'message' => 'Kertas Kerja Audit (KKA) berhasil diduplikasi sebagai Draft.',
            'data' => $copy,
        ], 201);
    }

    public function updateStatus(Request $request, Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah status KKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(Kka::STATUSES)],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus berupa Draft atau Final.',
        ]);

        $kka->update(['status' => $validated['status']]);

        return response()->json([
            'message' => "Status KKA berhasil diubah menjadi {$kka->status}.",
            'status' => $kka->status,
            'data' => $kka,
        ]);
    }

    public function getReviews(Request $request, Kka $kka, string $type): JsonResponse
    {
        if (! in_array($type, KkaReview::TYPES, true)) {
            return response()->json(['message' => 'Tipe reviu tidak valid.'], 422);
        }

        if (! $this->canAccessKka($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk KKA ini.',
            ], 403);
        }

        $kka->load([
            'pka:id,sprin_id,nomor_pka,bidang,jenis_audit',
            'pka.sprin:id,no_sprin,tahap,sprin_tipe,plotting_id,kode_wilayah,dalnis,daltu,katim,katim_2',
            'dibuatOleh:id,name',
            'creator:id,name',
        ]);

        $reviews = $kka->reviews()
            ->where('role_type', $type)
            ->with('user:id,name,role')
            ->get();

        $latestReview = $reviews->first();
        $approvedDate = $kka->{"approved_by_{$type}_date"};
        $latestStatus = $latestReview?->status ?? ($approvedDate ? KkaReview::STATUS_DISETUJUI : null);
        $latestDate = $latestReview?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i')
            ?? $approvedDate?->timezone('Asia/Jakarta')->format('d/m/Y H:i');

        return response()->json([
            'kka' => [
                'id' => $kka->id,
                'nomor_kka' => $kka->nomor_kka,
                'nomor_pka' => $kka->pka?->nomor_pka ?? '-',
                'no_sprin' => $kka->pka?->sprin?->no_sprin ?? '-',
                'sprin_tipe' => $kka->pka?->sprin?->sprin_tipe ?? '-',
                'tahap' => $kka->pka?->sprin?->tahap ?? '-',
                'bidang' => $kka->pka?->bidang,
                'jenis_audit' => $kka->pka?->jenis_audit,
                'auditor_name' => $kka->dibuatOleh?->name ?? $kka->creator?->name ?? '-',
                'tempat' => $kka->tempat,
                'tanggal_kka_formatted' => $kka->tanggal_kka?->format('d/m/Y') ?? '-',
                'status' => $kka->status ?? 'Draft',
                'review_type' => $type,
                'role_label' => $this->reviewRoleLabel($type),
                'can_review' => KkaDataTable::canUserReview($request->user(), $kka, $type),
                'latest_review_status' => $latestStatus,
                'latest_review_date' => $latestDate,
            ],
            'reviews' => $reviews->map(fn (KkaReview $r): array => $this->formatReview($r))->values()->all(),
        ]);
    }

    public function storeReview(Request $request, Kka $kka, string $type): JsonResponse
    {
        if (! in_array($type, KkaReview::TYPES, true)) {
            return response()->json(['message' => 'Tipe reviu tidak valid.'], 422);
        }

        $user = $request->user();

        if (! KkaDataTable::canUserReview($user, $kka, $type)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk melakukan Reviu '.$this->reviewShortLabel($type).' pada KKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(KkaReview::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status reviu wajib dipilih.',
            'status.in' => 'Status reviu tidak valid.',
        ]);

        $dateColumn = "approved_by_{$type}_date";

        $review = DB::transaction(function () use ($kka, $user, $type, $validated, $dateColumn): KkaReview {
            $review = $kka->reviews()->create([
                'user_id' => $user->id,
                'role_type' => $type,
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $kka->update([
                $dateColumn => $validated['status'] === KkaReview::STATUS_DISETUJUI ? now() : null,
            ]);

            return $review;
        });

        $review->setRelation('user', $user);

        return response()->json([
            'message' => 'Reviu '.$this->reviewShortLabel($type)." KKA berhasil disimpan dengan status {$review->status}.",
            'review' => $this->formatReview($review),
            'review_type' => $type,
            'latest_status' => $review->status,
            'latest_review_date' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatReview(KkaReview $review): array
    {
        return [
            'id' => $review->id,
            'role_type' => $review->role_type,
            'status' => $review->status,
            'catatan' => $review->catatan ?? '-',
            'reviewer_name' => $review->user?->name ?? 'User',
            'created_at' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ];
    }

    protected function reviewRoleLabel(string $type): string
    {
        return match ($type) {
            KkaReview::TYPE_KATIM => 'Ketua Tim (Katim)',
            KkaReview::TYPE_DALNIS => 'Pengawas Tim (Dalnis)',
            default => 'Pengendali Mutu (Daltu)',
        };
    }

    protected function reviewShortLabel(string $type): string
    {
        return match ($type) {
            KkaReview::TYPE_KATIM => 'Katim',
            KkaReview::TYPE_DALNIS => 'Dalnis',
            default => 'Daltu',
        };
    }

    protected function options(Request $request): JsonResponse
    {
        $type = (string) $request->query('options');
        $parent = (string) $request->query('parent');

        $items = match ($type) {
            'cities' => $this->regionOptions(4, 'kabupaten_kota', $parent),
            'districts' => $this->regionOptions(6, 'kecamatan', $parent),
            'villages' => $this->regionOptions(10, 'desa_kelurahan', $parent),
            default => collect(),
        };

        return response()->json($items);
    }

    /**
     * @return Collection<int, object>
     */
    protected function regionOptions(int $codeLength, string $nameColumn, string $parent): Collection
    {
        return DB::table('wilayah')
            ->whereRaw('LENGTH(kode_wilayah) = ?', [$codeLength])
            ->where('kode_wilayah', 'like', $parent.'%')
            ->whereNull('deleted_at')
            ->orderBy('kode_wilayah')
            ->get(['kode_wilayah as code', "{$nameColumn} as name"]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildPayload(array $validated): array
    {
        $pka = Pka::query()->findOrFail((int) $validated['pka_id']);
        $isNasional = $validated['auditee_level'] === 'Nasional';

        $regionCode = $isNasional
            ? null
            : ($validated['village_code'] ?? $validated['district_code'] ?? $validated['city_code'] ?? $validated['province_code'] ?? null);

        return [
            'nomor_kka' => $validated['nomor_kka'],
            'pka_id' => $pka->id,
            'dibuat_oleh_id' => $pka->dibuat_oleh_id,
            'direviu_oleh_id' => $pka->direviu_oleh_id,
            'tanggal_dibuat' => $validated['tanggal_dibuat'] ?? null,
            'tanggal_direviu' => $validated['tanggal_direviu'] ?? null,
            'tanggal_kka' => $validated['tanggal_kka'],
            'tempat' => $validated['tempat'],
            'auditee_level' => $validated['auditee_level'],
            'auditee_plotting_id' => $isNasional ? (int) $validated['auditee_plotting_id'] : null,
            'auditee_kode_wilayah' => $regionCode,
            'template_kka_id' => ! empty($validated['template_kka_id']) ? (int) $validated['template_kka_id'] : null,
            'tujuan' => $validated['tujuan'] ?? null,
            'langkah' => $validated['langkah'] ?? null,
            'hasil' => $validated['hasil'] ?? null,
            'status' => $validated['status'],
        ];
    }

    protected function generateDuplicateNomorKka(string $nomorKka): string
    {
        $base = trim((string) preg_replace('/\s*\((?:Copy|Salinan)(?:\s+\d+)?\)$/i', '', $nomorKka));
        $candidate = "{$base} (Salinan)";
        $counter = 2;

        while (Kka::query()->where('nomor_kka', $candidate)->exists()) {
            $candidate = "{$base} (Salinan {$counter})";
            $counter++;
        }

        return $candidate;
    }

    protected function canAccessPkaId(?User $user, int $pkaId): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        return Pka::query()->visibleTo($user)->whereKey($pkaId)->exists();
    }

    protected function canAccessKka(?User $user, Kka $kka): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        if ($kka->created_by && (int) $kka->created_by === (int) $user->id) {
            return true;
        }

        return $this->canAccessPkaId($user, (int) $kka->pka_id);
    }
}
