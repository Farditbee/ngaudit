<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\KkaTabulasiDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\KkaTabulasiRequest;
use App\Models\Kka;
use App\Models\KkaTabulasi;
use App\Models\Kode;
use App\Models\Kriteria;
use App\Models\SubKode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KkaTabulasiController extends Controller
{
    public function index(Request $request, Kka $kka, string $tab = 'tabulasi', ?KkaTabulasiDataTable $dataTable = null): View|JsonResponse
    {
        $dataTable ??= app(KkaTabulasiDataTable::class);

        if (! $this->canAccessKka($request->user(), $kka)) {
            abort(403, 'Anda tidak memiliki hak akses untuk KKA ini.');
        }

        $jenisTabulasi = match ($tab) {
            'konsultasi' => 'Konsultasi',
            'positif' => 'Positif',
            default => 'Temuan',
        };

        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request, $kka, $jenisTabulasi);
        }

        $kka->load([
            'pka:id,sprin_id,nomor_pka,bidang,jenis_audit,tanggal,anggaran',
            'pka.sprin.pengendaliMutu:id,name,username',
            'pka.sprin.pengawasTim:id,name,username',
            'pka.sprin.ketuaTim:id,name,username',
            'pka.sprin.ketuaTim2:id,name,username',
            'pka.sprin.tim.auditors:id,name,username,role',
            'dibuatOleh:id,name,username',
            'direviuOleh:id,name,username',
            'auditeePlotting:id,nama_unit',
        ]);

        $auditors = $this->resolveAuditors($kka);

        $kodes = Kode::query()
            ->with(['subKodes' => fn ($q) => $q->orderBy('kode')])
            ->orderBy('kode')
            ->get()
            ->groupBy('jenis');

        $mapKode = fn (Kode $k): array => [
            'id' => (int) $k->id,
            'code' => (string) $k->id,
            'kode' => (string) $k->kode,
            'nama' => (string) $k->nama,
            'name' => "({$k->kode}) - {$k->nama}",
            'kode_temuan_id' => $k->kode_temuan_id ? (string) $k->kode_temuan_id : '',
            'sub_kodes' => $k->subKodes->map(fn (SubKode $s): array => [
                'id' => (int) $s->id,
                'code' => (string) $s->id,
                'kode_id' => (string) $s->kode_id,
                'kode' => (string) $s->kode,
                'nama' => (string) $s->nama,
                'name' => "({$s->kode}) - {$s->nama}",
            ])->values()->all(),
        ];

        $kriterias = Kriteria::query()
            ->orderBy('nama')
            ->get()
            ->map(fn (Kriteria $kr): array => [
                'id' => (int) $kr->id,
                'code' => (string) $kr->id,
                'nama' => (string) $kr->nama,
                'name' => (string) $kr->nama,
                'is_opsnal' => (bool) $kr->is_opsnal,
                'is_logistik' => (bool) $kr->is_logistik,
                'is_sdm' => (bool) $kr->is_sdm,
                'is_garkeu' => (bool) $kr->is_garkeu,
            ])
            ->values()
            ->all();

        $masterData = [
            'auditors' => $auditors,
            'kodeTemuans' => ($kodes->get(Kode::JENIS_TEMUAN) ?? collect())->map($mapKode)->values()->all(),
            'kodeSebabs' => ($kodes->get(Kode::JENIS_SEBAB) ?? collect())->map($mapKode)->values()->all(),
            'kodeAkibats' => ($kodes->get(Kode::JENIS_AKIBAT) ?? collect())->map($mapKode)->values()->all(),
            'kodeRekomendasis' => ($kodes->get(Kode::JENIS_REKOMENDASI) ?? collect())->map($mapKode)->values()->all(),
            'kriterias' => $kriterias,
        ];

        $tabCounts = KkaTabulasi::query()
            ->where('kka_id', $kka->id)
            ->selectRaw('jenis_tabulasi, COUNT(*) as aggregate')
            ->groupBy('jenis_tabulasi')
            ->pluck('aggregate', 'jenis_tabulasi');

        return view('audit.kka.tabulasi', [
            'kka' => $kka,
            'tabSlug' => $tab,
            'jenisTabulasi' => $jenisTabulasi,
            'tabCounts' => [
                'Temuan' => (int) ($tabCounts['Temuan'] ?? 0),
                'Konsultasi' => (int) ($tabCounts['Konsultasi'] ?? 0),
                'Positif' => (int) ($tabCounts['Positif'] ?? 0),
            ],
            'auditors' => $auditors,
            'tableConfig' => $dataTable->config($kka, $tab, $jenisTabulasi, $masterData),
        ]);
    }

    public function store(KkaTabulasiRequest $request, Kka $kka): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk KKA ini.',
            ], 403);
        }

        $payload = $this->buildPayload($request->validated(), $kka);
        $payload['created_by'] = $request->user()?->id;

        $tabulasi = $kka->tabulasis()->create($payload);

        return response()->json([
            'message' => 'Data tabulasi KKA berhasil ditambahkan.',
            'data' => $tabulasi,
        ], 201);
    }

    public function show(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk tabulasi KKA ini.',
            ], 403);
        }

        return response()->json([
            'id' => $tabulasi->id,
            'jenis_tabulasi' => $tabulasi->jenis_tabulasi ?? 'Temuan',
            'bidang' => $tabulasi->bidang ?? ($kka->pka?->bidang ?? ''),
            'nomor' => $tabulasi->nomor ?? '',
            'tipe_temuan' => $tabulasi->tipe_temuan ?? '',
            'kode_temuan_id' => $tabulasi->kode_temuan_id ? (string) $tabulasi->kode_temuan_id : '',
            'sub_kode_temuan_id' => $tabulasi->sub_kode_temuan_id ? (string) $tabulasi->sub_kode_temuan_id : '',
            'nilai_temuan' => $tabulasi->nilai_temuan !== null ? (string) (float) $tabulasi->nilai_temuan : '',
            'uraian_temuan' => $tabulasi->uraian_temuan ?? '',
            'kriteria_id' => $tabulasi->kriteria_id ? (string) $tabulasi->kriteria_id : '',
            'uraian_kriteria' => $tabulasi->uraian_kriteria ?? '',
            'kode_sebab_id' => $tabulasi->kode_sebab_id ? (string) $tabulasi->kode_sebab_id : '',
            'sub_kode_sebab_id' => $tabulasi->sub_kode_sebab_id ? (string) $tabulasi->sub_kode_sebab_id : '',
            'uraian_sebab' => $tabulasi->uraian_sebab ?? '',
            'kode_akibat_id' => $tabulasi->kode_akibat_id ? (string) $tabulasi->kode_akibat_id : '',
            'sub_kode_akibat_id' => $tabulasi->sub_kode_akibat_id ? (string) $tabulasi->sub_kode_akibat_id : '',
            'uraian_akibat' => $tabulasi->uraian_akibat ?? '',
            'kode_rekomendasi_id' => $tabulasi->kode_rekomendasi_id ? (string) $tabulasi->kode_rekomendasi_id : '',
            'sub_kode_rekomendasi_id' => $tabulasi->sub_kode_rekomendasi_id ? (string) $tabulasi->sub_kode_rekomendasi_id : '',
            'uraian_rekomendasi' => $tabulasi->uraian_rekomendasi ?? '',
            'auditor_id' => $tabulasi->auditor_id ? (string) $tabulasi->auditor_id : '',
            'status' => $tabulasi->status ?? 'Draft',
        ]);
    }

    public function update(KkaTabulasiRequest $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah tabulasi KKA ini.',
            ], 403);
        }

        $tabulasi->update($this->buildPayload($request->validated(), $kka));

        return response()->json([
            'message' => 'Data tabulasi KKA berhasil diperbarui.',
            'data' => $tabulasi,
        ]);
    }

    public function destroy(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus tabulasi KKA ini.',
            ], 403);
        }

        $tabulasi->delete();

        return response()->json([
            'message' => 'Data tabulasi KKA berhasil dihapus.',
        ]);
    }

    public function updateStatus(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengubah status tabulasi KKA ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(KkaTabulasi::STATUSES)],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus berupa Draft atau Final.',
        ]);

        $tabulasi->update(['status' => $validated['status']]);

        return response()->json([
            'message' => "Status tabulasi berhasil diubah menjadi {$tabulasi->status}.",
            'status' => $tabulasi->status,
            'data' => $tabulasi,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildPayload(array $validated, Kka $kka): array
    {
        $kodeTemuanId = ! empty($validated['kode_temuan_id']) ? (int) $validated['kode_temuan_id'] : null;
        $hasNilai = false;

        if ($kodeTemuanId) {
            $kodeStr = Kode::query()->whereKey($kodeTemuanId)->value('kode');
            $hasNilai = in_array((string) $kodeStr, KkaTabulasi::KODE_TEMUAN_WITH_NILAI, true);
        }

        return [
            'jenis_tabulasi' => $validated['jenis_tabulasi'],
            'bidang' => $kka->pka?->bidang,
            'nomor' => trim((string) $validated['nomor']),
            'tipe_temuan' => ! empty($validated['tipe_temuan']) ? (string) $validated['tipe_temuan'] : null,
            'kode_temuan_id' => $kodeTemuanId,
            'sub_kode_temuan_id' => ! empty($validated['sub_kode_temuan_id']) ? (int) $validated['sub_kode_temuan_id'] : null,
            'nilai_temuan' => ($hasNilai && isset($validated['nilai_temuan']) && $validated['nilai_temuan'] !== '')
                ? (float) $validated['nilai_temuan']
                : null,
            'uraian_temuan' => $validated['uraian_temuan'] ?? null,
            'kriteria_id' => ! empty($validated['kriteria_id']) ? (int) $validated['kriteria_id'] : null,
            'uraian_kriteria' => $validated['uraian_kriteria'] ?? null,
            'kode_sebab_id' => ! empty($validated['kode_sebab_id']) ? (int) $validated['kode_sebab_id'] : null,
            'sub_kode_sebab_id' => ! empty($validated['sub_kode_sebab_id']) ? (int) $validated['sub_kode_sebab_id'] : null,
            'uraian_sebab' => $validated['uraian_sebab'] ?? null,
            'kode_akibat_id' => ! empty($validated['kode_akibat_id']) ? (int) $validated['kode_akibat_id'] : null,
            'sub_kode_akibat_id' => ! empty($validated['sub_kode_akibat_id']) ? (int) $validated['sub_kode_akibat_id'] : null,
            'uraian_akibat' => $validated['uraian_akibat'] ?? null,
            'kode_rekomendasi_id' => ! empty($validated['kode_rekomendasi_id']) ? (int) $validated['kode_rekomendasi_id'] : null,
            'sub_kode_rekomendasi_id' => ! empty($validated['sub_kode_rekomendasi_id']) ? (int) $validated['sub_kode_rekomendasi_id'] : null,
            'uraian_rekomendasi' => $validated['uraian_rekomendasi'] ?? null,
            'auditor_id' => ! empty($validated['auditor_id']) ? (int) $validated['auditor_id'] : null,
            'status' => $validated['status'] ?? 'Draft',
        ];
    }

    /**
     * @return list<array{id: int, code: string, name: string, label: string}>
     */
    protected function resolveAuditors(Kka $kka): array
    {
        $sprin = $kka->pka?->sprin;
        $users = collect();

        if ($sprin?->ketuaTim) {
            $users->push($sprin->ketuaTim);
        }
        if ($sprin?->ketuaTim2) {
            $users->push($sprin->ketuaTim2);
        }
        if ($sprin?->tim && $sprin->tim->auditors) {
            foreach ($sprin->tim->auditors as $auditor) {
                $users->push($auditor);
            }
        }
        if ($kka->dibuatOleh) {
            $users->push($kka->dibuatOleh);
        }

        if ($users->isEmpty()) {
            $users = User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'username']);
        }

        return $users
            ->unique('id')
            ->map(function (User $u): array {
                $selectName = $u->username ? "{$u->username} | {$u->name}" : (string) $u->name;

                return [
                    'id' => (int) $u->id,
                    'code' => (string) $u->id,
                    'name' => $selectName,
                    'label' => (string) $u->name,
                ];
            })
            ->values()
            ->all();
    }

    protected function canAccessKka(?User $user, Kka $kka): bool
    {
        if (! $user) {
            return false;
        }

        return Kka::query()->whereKey($kka->id)->visibleTo($user)->exists();
    }
}
