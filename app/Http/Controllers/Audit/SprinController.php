<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\SprinDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\SprinRequest;
use App\Models\Sprin;
use App\Models\SprinReview;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SprinController extends Controller
{
    public function index(Request $request, SprinDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        $tims = Tim::query()
            ->visibleTo($request->user())
            ->with(['plotting:id,plot', 'province:id,kode_wilayah,provinsi', 'daltu:id,name', 'dalnis:id,name', 'ketuaTim:id,name', 'ketuaTim2:id,name', 'auditors:id,name'])
            ->orderBy('nama_tim')
            ->get()
            ->map(fn (Tim $tim): array => [
                'id' => $tim->id,
                'code' => (string) $tim->id,
                'name' => $tim->nama_tim,
                'lingkup' => $tim->lingkup,
                'scope_name' => $tim->lingkup === 'Nasional' ? $tim->plotting?->plot : $tim->province?->provinsi,
                'daltu_name' => $tim->daltu?->name,
                'dalnis_name' => $tim->dalnis?->name,
                'ketua_tim_name' => $tim->ketuaTim?->name,
                'ketua_tim_2_name' => $tim->ketuaTim2?->name,
                'auditors' => $tim->auditors->pluck('name')->values()->all(),
            ])
            ->values()
            ->all();

        $tableConfig = $dataTable->config($tims);

        return view('audit.sprin.index', compact('tableConfig'));
    }

    public function store(SprinRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $sprin = Sprin::query()->create(array_merge(
            $this->buildPayload($validated),
            $this->storeFiles($request),
        ));

        return response()->json([
            'message' => 'Data Sprin berhasil ditambahkan.',
            'data' => $sprin,
        ], 201);
    }

    public function update(SprinRequest $request, Sprin $sprin): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $sprin, $validated): void {
            $sprin->update(array_merge(
                $this->buildPayload($validated),
                $this->storeFiles($request, $sprin),
            ));
        });

        return response()->json([
            'message' => 'Data Sprin berhasil diperbarui.',
            'data' => $sprin->fresh(),
        ]);
    }

    public function destroy(Sprin $sprin): JsonResponse
    {
        $sprin->delete();

        return response()->json([
            'message' => 'Data Sprin berhasil dihapus.',
        ]);
    }

    public function getReviews(Request $request, Sprin $sprin): JsonResponse
    {
        if (! $this->canAccessSprin($request->user(), $sprin)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk Sprin ini.',
            ], 403);
        }

        $sprin->load(['reviews.user:id,name,role']);

        return response()->json([
            'sprin' => [
                'id' => $sprin->id,
                'no_sprin' => $sprin->no_sprin,
                'sprin_tipe' => $sprin->sprin_tipe,
                'plot_name' => $sprin->plotName(),
                'province_name' => $sprin->provinceName(),
                'can_review' => $this->canReviewSprin($request->user(), $sprin),
                'tgl_mulai' => $sprin->tgl_mulai->format('Y-m-d'),
                'tgl_akhir' => $sprin->tgl_akhir->format('Y-m-d'),
                'file_surat_perintah_name' => $sprin->file_surat_perintah_name,
                'surat_perintah_url' => $sprin->suratPerintahUrl(),
                'file_surat_pemberitahuan_name' => $sprin->file_surat_pemberitahuan_name,
                'surat_pemberitahuan_url' => $sprin->suratPemberitahuanUrl(),
                'keterangan' => $sprin->keterangan ?? '-',
                'approved_by_daltu_date' => $sprin->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            ],
            'reviews' => $sprin->reviews->map(fn (SprinReview $r): array => [
                'id' => $r->id,
                'status' => $r->status,
                'catatan' => $r->catatan ?? '-',
                'reviewer_name' => $r->user?->name ?? 'User',
                'created_at' => $r->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            ])->values()->all(),
        ]);
    }

    public function storeReview(Request $request, Sprin $sprin): JsonResponse
    {
        $user = $request->user();

        if (! $this->canReviewSprin($user, $sprin)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mereviu Sprin ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(SprinReview::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status reviu wajib dipilih.',
            'status.in' => 'Status reviu tidak valid.',
        ]);

        $review = DB::transaction(function () use ($sprin, $user, $validated): SprinReview {
            $review = $sprin->reviews()->create([
                'user_id' => $user->id,
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            if ($validated['status'] === SprinReview::STATUS_DISETUJUI) {
                $sprin->update(['approved_by_daltu_date' => now()]);
            } else {
                $sprin->update(['approved_by_daltu_date' => null]);
            }

            return $review;
        });

        return response()->json([
            'message' => "Reviu Sprin berhasil disimpan dengan status {$review->status}.",
            'review' => [
                'id' => $review->id,
                'status' => $review->status,
                'catatan' => $review->catatan ?? '-',
                'reviewer_name' => $user->name,
                'created_at' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            ],
            'latest_status' => $review->status,
            'latest_review_date' => $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            'approved_by_daltu_date' => $sprin->fresh()->approved_by_daltu_date?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ], 201);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildPayload(array $validated): array
    {
        $tim = Tim::query()->findOrFail($validated['tim_id']);

        return [
            'no_sprin' => $validated['no_sprin'],
            'no_surat_pemberitahuan' => $validated['no_surat_pemberitahuan'],
            'tgl_mulai' => $validated['tgl_mulai'],
            'tgl_akhir' => $validated['tgl_akhir'],
            'tim_id' => $tim->id,
            'plotting_id' => $tim->lingkup === 'Nasional' ? $tim->plotting_id : null,
            'kode_wilayah' => $tim->lingkup === 'Wilayah' ? $tim->province_code : null,
            'katim' => $tim->ketua_tim_id,
            'katim_2' => $tim->ketua_tim_2_id,
            'dalnis' => $tim->dalnis_id,
            'daltu' => $tim->daltu_id,
            'tahap' => $validated['tahap'],
            'sprin_tipe' => $tim->lingkup,
            'keterangan' => $validated['keterangan'] ?? null,
        ];
    }

    /**
     * Simpan dokumen yang diunggah dan hapus dokumen lama bila diganti.
     *
     * @return array<string, string>
     */
    protected function storeFiles(Request $request, ?Sprin $sprin = null): array
    {
        $attributes = [];

        $map = [
            'file_surat_perintah' => ['file_surat_perintah_id', 'file_surat_perintah_name', 'sprin/surat-perintah'],
            'file_surat_pemberitahuan' => ['file_surat_pemberitahuan_id', 'file_surat_pemberitahuan_name', 'sprin/surat-pemberitahuan'],
        ];

        foreach ($map as $input => [$pathColumn, $nameColumn, $directory]) {
            if (! $request->hasFile($input)) {
                continue;
            }

            if ($sprin?->{$pathColumn}) {
                Storage::disk('public')->delete($sprin->{$pathColumn});
            }

            $file = $request->file($input);
            $attributes[$pathColumn] = $file->store($directory, 'public');
            $attributes[$nameColumn] = $file->getClientOriginalName();
        }

        return $attributes;
    }

    protected function canAccessSprin(?User $user, Sprin $sprin): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        if ($user->role === 'Admin Nasional') {
            return $user->plotting_id && (int) $sprin->plotting_id === (int) $user->plotting_id;
        }

        if ($user->role === 'Admin Wilayah') {
            $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

            return $sprin->sprin_tipe === 'Wilayah' && $sprin->kode_wilayah === $provinceCode;
        }

        if ($user->role === 'Koordinator dan Pengendali') {
            if ($user->plotting_id) {
                return (int) $sprin->plotting_id === (int) $user->plotting_id;
            }

            if ($user->kode_wilayah) {
                $provinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

                return $sprin->sprin_tipe === 'Wilayah' && $sprin->kode_wilayah === $provinceCode;
            }

            return (int) $user->id === (int) $sprin->daltu;
        }

        return (int) $user->id === (int) $sprin->katim
            || (int) $user->id === (int) $sprin->katim_2
            || (int) $user->id === (int) $sprin->dalnis
            || (int) $user->id === (int) $sprin->daltu
            || (int) $user->id === (int) $sprin->created_by
            || (bool) $sprin->tim?->auditors()->where('users.id', $user->id)->exists();
    }

    protected function canReviewSprin(?User $user, Sprin $sprin): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Super Admin') {
            return true;
        }

        if ($user->role === 'Koordinator dan Pengendali') {
            if ($user->plotting_id) {
                return (int) $sprin->plotting_id === (int) $user->plotting_id;
            }

            if ($user->kode_wilayah) {
                $authProvinceCode = substr(str_replace('.', '', (string) $user->kode_wilayah), 0, 2);

                return $sprin->sprin_tipe === 'Wilayah' && $sprin->kode_wilayah === $authProvinceCode;
            }

            return (int) $user->id === (int) $sprin->daltu;
        }

        return false;
    }
}
