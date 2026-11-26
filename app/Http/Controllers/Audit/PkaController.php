<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\PkaDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\PkaRequest;
use App\Models\Pka;
use App\Models\PkaReview;
use App\Models\Sprin;
use App\Models\TemplatePka;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PkaController extends Controller
{
    public function index(Request $request, PkaDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->toJson($request);
        }

        $authUser = $request->user();

        $sprins = Sprin::query()
            ->visibleTo($authUser)
            ->approved()
            ->with(['tim:id,nama_tim', 'plotting:id,plot', 'province:kode_wilayah,provinsi'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Sprin $s): array => [
                'id' => $s->id,
                'no_sprin' => $s->no_sprin,
                'label' => $s->no_sprin.' ('.($s->tim?->nama_tim ?? $s->sprin_tipe).')',
                'tgl_mulai' => $s->tgl_mulai?->format('Y-m-d'),
                'tgl_akhir' => $s->tgl_akhir?->format('Y-m-d'),
                'tim_name' => $s->tim?->nama_tim ?? '-',
                'tipe' => $s->sprin_tipe,
            ]);

        $templates = TemplatePka::query()
            ->visibleTo($authUser)
            ->orderBy('nama_template')
            ->get([
                'id',
                'nama_template',
                'lingkup',
                'pendahuluan',
                'tujuan',
                'ruang_lingkup',
                'sasaran',
                'waktu_pelaksanaan',
                'periode',
                'penutup',
            ]);

        return view('audit.pka.index', [
            'dataTableConfig' => $dataTable->config($sprins, $templates),
            'columns' => $dataTable->columns(),
            'bidangs' => Pka::BIDANGS,
            'jenisAudits' => Pka::JENIS_AUDITS,
            'statuses' => Pka::STATUSES,
            'sprins' => $sprins,
            'templates' => $templates,
        ]);
    }

    public function store(PkaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $payload = $this->buildPayload($validated);
        $payload['created_by'] = $request->user()?->id;

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $payload['lampiran_path'] = $file->store('pka/lampiran', 'public');
            $payload['lampiran_name'] = $file->getClientOriginalName();
        }

        $pka = Pka::query()->create($payload);

        return response()->json([
            'message' => 'Program Kerja Audit (PKA) berhasil ditambahkan.',
            'data' => $pka,
        ], 201);
    }

    public function show(Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka(auth()->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA ini.',
            ], 403);
        }

        $pka->load(['sprin:id,no_sprin,sprin_tipe,tim_id', 'sprin.tim:id,nama_tim', 'templatePka:id,nama_template']);

        return response()->json([
            'id' => $pka->id,
            'sprin_id' => $pka->sprin_id,
            'no_sprin' => $pka->sprin?->no_sprin ?? '-',
            'tim_name' => $pka->sprin?->tim?->nama_tim ?? '-',
            'nomor_pka' => $pka->nomor_pka,
            'tanggal' => $pka->tanggal?->format('Y-m-d'),
            'tanggal_formatted' => $pka->tanggal?->format('d/m/Y') ?? '-',
            'anggaran' => (float) $pka->anggaran,
            'formatted_anggaran' => $pka->formattedAnggaran(),
            'bidang' => $pka->bidang,
            'jenis_audit' => $pka->jenis_audit,
            'sasaran_auditi' => $pka->sasaran_auditi,
            'status' => $pka->status ?? 'Draft',
            'template_pka_id' => $pka->template_pka_id,
            'template_name' => $pka->templatePka?->nama_template ?? null,
            'lampiran_name' => $pka->lampiran_name,
            'lampiran_url' => $pka->lampiranUrl(),
            'pendahuluan' => $pka->pendahuluan ?? '',
            'tujuan' => $pka->tujuan ?? '',
            'ruang_lingkup' => $pka->ruang_lingkup ?? '',
            'sasaran' => $pka->sasaran ?? '',
            'waktu_pelaksanaan' => $pka->waktu_pelaksanaan ?? '',
            'periode' => $pka->periode ?? '',
            'penutup' => $pka->penutup ?? '',
            'created_at_formatted' => $pka->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ]);
    }

    public function update(PkaRequest $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah PKA ini.',
            ], 403);
        }

        $validated = $request->validated();
        $payload = $this->buildPayload($validated);

        if ($request->hasFile('lampiran')) {
            if ($pka->lampiran_path) {
                Storage::disk('public')->delete($pka->lampiran_path);
            }
            $file = $request->file('lampiran');
            $payload['lampiran_path'] = $file->store('pka/lampiran', 'public');
            $payload['lampiran_name'] = $file->getClientOriginalName();
        }

        $pka->update($payload);

        return response()->json([
            'message' => 'Program Kerja Audit (PKA) berhasil diperbarui.',
            'data' => $pka,
        ]);
    }

    public function destroy(Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka(auth()->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus PKA ini.',
            ], 403);
        }

        if ($pka->lampiran_path) {
            Storage::disk('public')->delete($pka->lampiran_path);
        }

        $pka->delete();

        return response()->json([
            'message' => 'Program Kerja Audit (PKA) berhasil dihapus.',
        ]);
    }

    public function duplicate(Request $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menduplikasi PKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'nomor_pka' => ['required', 'string', 'max:255'],
        ], [
            'nomor_pka.required' => 'Nomor PKA baru wajib diisi.',
        ]);

        $copy = DB::transaction(function () use ($pka, $validated, $request): Pka {
            $copy = $pka->replicate();
            $copy->nomor_pka = $validated['nomor_pka'];
            $copy->status = 'Draft';
            $copy->approved_by_dalnis_date = null;
            $copy->approved_by_daltu_date = null;
            $copy->created_by = $request->user()?->id;
            $copy->save();

            foreach ($pka->langkahKerjas()->orderBy('id')->get() as $lk) {
                $copy->langkahKerjas()->create([
                    'bidang' => $copy->bidang,
                    'uraian' => $lk->uraian,
                    'dilaksanakan_rencana_id' => $lk->dilaksanakan_rencana_id,
                    'dilaksanakan_realisasi_id' => $lk->dilaksanakan_realisasi_id,
                    'waktu_rencana' => $lk->waktu_rencana,
                    'waktu_realisasi' => $lk->waktu_realisasi,
                    'catatan' => $lk->catatan,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $copy;
        });

        return response()->json([
            'message' => 'Program Kerja Audit (PKA) berhasil diduplikasi.',
            'data' => $copy,
        ], 201);
    }

    public function updateStatus(Request $request, Pka $pka): JsonResponse
    {
        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah status PKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Draft,Final'],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus berupa Draft atau Final.',
        ]);

        $pka->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => "Status Dokumen PKA {$pka->nomor_pka} berhasil diubah menjadi {$pka->status}.",
            'status' => $pka->status,
            'data' => $pka,
        ]);
    }

    public function getReviews(Request $request, Pka $pka, string $type): JsonResponse
    {
        if (! in_array($type, PkaReview::TYPES, true)) {
            return response()->json(['message' => 'Tipe reviu tidak valid.'], 422);
        }

        if (! $this->canAccessPka($request->user(), $pka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk PKA ini.',
            ], 403);
        }

        $pka->load([
            'sprin:id,no_sprin,sprin_tipe,tahap,tim_id,plotting_id,kode_wilayah,dalnis,daltu',
            'sprin.tim:id,nama_tim',
            'sprin.plotting:id,plot',
            'sprin.province:id,kode_wilayah,provinsi',
        ]);

        $reviews = $pka->reviews()
            ->where('role_type', $type)
            ->with('user:id,name,role')
            ->get();

        $latestReview = $reviews->first();
        $approvedDate = $type === PkaReview::TYPE_DALNIS ? $pka->approved_by_dalnis_date : $pka->approved_by_daltu_date;
        $latestStatus = $latestReview?->status ?? ($approvedDate ? PkaReview::STATUS_DISETUJUI : null);
        $latestDate = $latestReview?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i')
            ?? $approvedDate?->timezone('Asia/Jakarta')->format('d/m/Y H:i');

        $roleLabel = $type === PkaReview::TYPE_DALNIS ? 'Pengawas Tim (Dalnis)' : 'Pengendali Mutu (Daltu)';

        return response()->json([
            'pka' => [
                'id' => $pka->id,
                'nomor_pka' => $pka->nomor_pka,
                'no_sprin' => $pka->sprin?->no_sprin ?? '-',
                'sprin_tipe' => $pka->sprin?->sprin_tipe ?? '-',
                'tahap' => $pka->sprin?->tahap ?? '-',
                'plot_name' => $pka->sprin?->plotName(),
                'province_name' => $pka->sprin?->provinceName(),
                'tim_name' => $pka->sprin?->tim?->nama_tim ?? '-',
                'bidang' => $pka->bidang,
                'jenis_audit' => $pka->jenis_audit,
                'sasaran_auditi' => $pka->sasaran_auditi,
                'tanggal_formatted' => $pka->tanggal?->format('d/m/Y') ?? '-',
                'formatted_anggaran' => $pka->formattedAnggaran(),
                'status' => $pka->status ?? 'Draft',
                'lampiran_name' => $pka->lampiran_name,
                'lampiran_url' => $pka->lampiranUrl(),
                'review_type' => $type,
                'role_label' => $roleLabel,
                'can_review' => $this->canReviewPka($request->user(), $pka, $type),
                'latest_review_status' => $latestStatus,
                'latest_review_date' => $latestDate,
            ],
            'reviews' => $reviews->map(fn (PkaReview $r): array => [
                'id' => $r->id,
                'role_type' => $r->role_type,
                'status' => $r->status,
                'catatan' => $r->catatan ?? '-',
                'reviewer_name' => $r->user?->name ?? 'User',
                'created_at' => $r->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            ])->values()->all(),
        ]);
    }

    public function storeReview(Request $request, Pka $pka, string $type): JsonResponse
    {
        if (! in_array($type, PkaReview::TYPES, true)) {
            return response()->json(['message' => 'Tipe reviu tidak valid.'], 422);
        }

        $user = $request->user();

        if (! $this->canReviewPka($user, $pka, $type)) {
            $label = $type === PkaReview::TYPE_DALNIS ? 'Dalnis' : 'Daltu';

            return response()->json([
                'message' => "Anda tidak memiliki hak akses untuk melakukan Reviu {$label} pada PKA ini.",
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(PkaReview::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status reviu wajib dipilih.',
            'status.in' => 'Status reviu tidak valid.',
        ]);

        $dateColumn = $type === PkaReview::TYPE_DALNIS ? 'approved_by_dalnis_date' : 'approved_by_daltu_date';

        $review = DB::transaction(function () use ($pka, $user, $type, $validated, $dateColumn): PkaReview {
            $review = $pka->reviews()->create([
                'user_id' => $user->id,
                'role_type' => $type,
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            if ($validated['status'] === PkaReview::STATUS_DISETUJUI) {
                $pka->update([$dateColumn => now()]);
            } else {
                $pka->update([$dateColumn => null]);
            }

            return $review;
        });

        $label = $type === PkaReview::TYPE_DALNIS ? 'Dalnis' : 'Daltu';

        return response()->json([
            'message' => "Reviu {$label} PKA berhasil disimpan dengan status {$review->status}.",
            'review' => [
                'id' => $review->id,
                'role_type' => $review->role_type,
                'status' => $review->status,
                'catatan' => $review->catatan ?? '-',
                'reviewer_name' => $user->name,
                'created_at' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            ],
            'review_type' => $type,
            'latest_status' => $review->status,
            'latest_review_date' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ], 201);
    }

    protected function canReviewPka(?User $user, Pka $pka, string $type): bool
    {
        $pka->loadMissing('sprin:id,sprin_tipe,plotting_id,kode_wilayah,dalnis,daltu');

        return PkaDataTable::canUserReview($user, $pka, $type);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildPayload(array $validated): array
    {
        return [
            'sprin_id' => (int) $validated['sprin_id'],
            'nomor_pka' => $validated['nomor_pka'],
            'tanggal' => $validated['tanggal'],
            'anggaran' => (float) $validated['anggaran'],
            'bidang' => $validated['bidang'],
            'jenis_audit' => $validated['jenis_audit'],
            'sasaran_auditi' => $validated['sasaran_auditi'],
            'status' => $validated['status'] ?? 'Draft',
            'template_pka_id' => ! empty($validated['template_pka_id']) ? (int) $validated['template_pka_id'] : null,
            'pendahuluan' => $validated['pendahuluan'] ?? null,
            'tujuan' => $validated['tujuan'] ?? null,
            'ruang_lingkup' => $validated['ruang_lingkup'] ?? null,
            'sasaran' => $validated['sasaran'] ?? null,
            'waktu_pelaksanaan' => $validated['waktu_pelaksanaan'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'penutup' => $validated['penutup'] ?? null,
        ];
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
