<?php

namespace App\Http\Controllers\Audit;

use App\DataTables\Audit\TindakLanjutAuditeeDataTable;
use App\Http\Controllers\Controller;
use App\Models\Kka;
use App\Models\KkaTabulasi;
use App\Models\KkaTabulasiLampiran;
use App\Models\KkaTabulasiMessage;
use App\Models\Kode;
use App\Models\SubKode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TindakLanjutAuditeeController extends Controller
{
    public function index(Request $request, TindakLanjutAuditeeDataTable $dataTable): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $dataTable->toJson($request);
        }

        return view('audit.tindak-lanjut.index', [
            'dataTableConfig' => $dataTable->config(),
            'columns' => $dataTable->columns(),
        ]);
    }

    public function show(Request $request, Kka $kka, string $tab = 'tabulasi'): View|JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            abort(403, 'Anda tidak memiliki hak akses untuk KKA ini.');
        }

        $jenisTabulasi = match ($tab) {
            'konsultasi' => 'Konsultasi',
            'positif' => 'Positif',
            default => 'Temuan',
        };

        $kka->load([
            'pka:id,sprin_id,nomor_pka,bidang,jenis_audit,tanggal,anggaran',
            'pka.sprin:id,no_sprin,tahap,sprin_tipe',
            'dibuatOleh:id,name,username',
            'creator:id,name,username',
            'auditeePlotting:id,plot',
        ]);

        $items = KkaTabulasi::query()
            ->where('kka_id', $kka->id)
            ->where('jenis_tabulasi', $jenisTabulasi)
            ->with([
                'kodeTemuan:id,kode,nama',
                'subKodeTemuan:id,kode_id,kode,nama',
                'kodeSebab:id,kode,nama',
                'subKodeSebab:id,kode_id,kode,nama',
                'kodeAkibat:id,kode,nama',
                'subKodeAkibat:id,kode_id,kode,nama',
                'kodeRekomendasi:id,kode,nama',
                'subKodeRekomendasi:id,kode_id,kode,nama',
                'kodeTinjut:id,kode,nama',
                'subKodeTinjut:id,kode_id,kode,nama',
                'auditor:id,name,username',
                'messages.user:id,name,role',
                'lampirans.user:id,name,role',
            ])
            ->orderBy('nomor')
            ->orderBy('id')
            ->get()
            ->map(fn (KkaTabulasi $t): array => $this->formatTabulasiRow($t))
            ->values()
            ->all();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['data' => $items]);
        }

        $kodeTinjuts = Kode::query()
            ->where('jenis', Kode::JENIS_TINJUT)
            ->with(['subKodes' => fn ($q) => $q->orderBy('kode')])
            ->orderBy('kode')
            ->get()
            ->map(fn (Kode $k): array => [
                'id' => (int) $k->id,
                'code' => (string) $k->id,
                'kode' => (string) $k->kode,
                'nama' => (string) $k->nama,
                'name' => "({$k->kode}) - {$k->nama}",
                'sub_kodes' => $k->subKodes->map(fn (SubKode $s): array => [
                    'id' => (int) $s->id,
                    'code' => (string) $s->id,
                    'kode_id' => (string) $s->kode_id,
                    'kode' => (string) $s->kode,
                    'nama' => (string) $s->nama,
                    'name' => "({$s->kode}) - {$s->nama}",
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $tabCounts = KkaTabulasi::query()
            ->where('kka_id', $kka->id)
            ->selectRaw('jenis_tabulasi, COUNT(*) as aggregate')
            ->groupBy('jenis_tabulasi')
            ->pluck('aggregate', 'jenis_tabulasi');

        $auditeeName = $this->resolveAuditeeName($kka);
        $noKka = $kka->nomor_kka ?: ('KKA '.$kka->tanggal_kka?->format('d/m/Y'));
        $auditorName = $kka->dibuatOleh?->name ?? $kka->creator?->name ?? '-';
        $authUser = $request->user();

        return view('audit.tindak-lanjut.show', [
            'kka' => $kka,
            'tabSlug' => $tab,
            'jenisTabulasi' => $jenisTabulasi,
            'auditeeName' => $auditeeName,
            'auditorName' => $auditorName,
            'tabCounts' => [
                'Temuan' => (int) ($tabCounts['Temuan'] ?? 0),
                'Konsultasi' => (int) ($tabCounts['Konsultasi'] ?? 0),
                'Positif' => (int) ($tabCounts['Positif'] ?? 0),
            ],
            'pageConfig' => [
                'kkaId' => $kka->id,
                'baseUrl' => url("/audit/tindak-lanjut/{$kka->id}"),
                'jenisTabulasi' => $jenisTabulasi,
                'auditeeName' => $auditeeName,
                'noKka' => $noKka,
                'auditorName' => $auditorName,
                'bidang' => $kka->pka?->bidang ?? '-',
                'canUjiNilaiRole' => $this->isAuditorOrReviewer($authUser),
                'userRole' => $authUser?->role ?? '',
                'items' => $items,
                'kodeTinjuts' => $kodeTinjuts,
                'statusTinjuts' => KkaTabulasi::STATUS_TINJUTS,
                'kesimpulanTinjuts' => KkaTabulasi::KESIMPULAN_TINJUTS,
            ],
        ]);
    }

    /**
     * Simpan Tindak Lanjut Auditee pada Tab Informasi (Kode Tinjut, Sub Kode Tinjut, Isi Tindak Lanjut, Nilai Pengembalian).
     */
    public function storeTanggapan(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menanggapi tabulasi KKA ini.',
            ], 403);
        }

        if ($request->has('nilai_pengembalian') && is_string($request->input('nilai_pengembalian'))) {
            $cleaned = preg_replace('/[^\d]/', '', (string) $request->input('nilai_pengembalian'));
            $request->merge([
                'nilai_pengembalian' => $cleaned !== '' ? $cleaned : null,
            ]);
        }

        $validated = $request->validate([
            'status_tinjut' => ['nullable', 'string', Rule::in(KkaTabulasi::STATUS_TINJUTS)],
            'kode_tinjut_id' => [
                'nullable',
                'integer',
                Rule::exists('kodes', 'id')->where('jenis', Kode::JENIS_TINJUT)->whereNull('deleted_at'),
            ],
            'sub_kode_tinjut_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_kodes', 'id')->whereNull('deleted_at'),
            ],
            'tindak_lanjut' => ['required', 'string'],
            'nilai_pengembalian' => ['nullable', 'numeric', 'min:0'],
            'kesimpulan_tinjut' => ['nullable', 'string', 'max:100'],
            'alasan_tinjut' => ['nullable', 'string', 'max:2000'],
        ], [
            'tindak_lanjut.required' => 'Isi tindak lanjut wajib diisi.',
        ]);

        $nextStatus = $validated['status_tinjut'] ?? $tabulasi->status_tinjut;
        if (! $nextStatus || $nextStatus === 'Belum Ditinjut') {
            $nextStatus = 'Dalam Proses';
        }

        $payload = [
            'status_tinjut' => $nextStatus,
            'kode_tinjut_id' => ! empty($validated['kode_tinjut_id']) ? (int) $validated['kode_tinjut_id'] : null,
            'sub_kode_tinjut_id' => ! empty($validated['sub_kode_tinjut_id']) ? (int) $validated['sub_kode_tinjut_id'] : null,
            'tindak_lanjut' => $validated['tindak_lanjut'],
            'nilai_pengembalian' => isset($validated['nilai_pengembalian']) && $validated['nilai_pengembalian'] !== ''
                ? (float) $validated['nilai_pengembalian']
                : null,
        ];

        if ($this->isAuditorOrReviewer($request->user())) {
            if (array_key_exists('kesimpulan_tinjut', $validated)) {
                $payload['kesimpulan_tinjut'] = $validated['kesimpulan_tinjut'];
            }
            if (array_key_exists('alasan_tinjut', $validated)) {
                $payload['alasan_tinjut'] = $validated['alasan_tinjut'];
            }
        }

        $tabulasi->update($payload);

        return response()->json([
            'message' => 'Tindak lanjut auditee berhasil disimpan.',
            'data' => $this->reloadAndFormat($tabulasi),
        ]);
    }

    /**
     * Simpan Uji Nilai (Kesimpulan, Alasan, Status) — hanya oleh Auditor apabila sudah ditinjut oleh Auditee.
     */
    public function storeUjiNilai(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk tabulasi KKA ini.',
            ], 403);
        }

        if (! $this->isAuditorOrReviewer($request->user())) {
            return response()->json([
                'message' => 'Hanya akun dengan role Auditor yang dapat mengisi Uji Nilai (Kesimpulan, Alasan, dan Status).',
            ], 403);
        }

        $hasTinjut = ! empty(trim(strip_tags((string) $tabulasi->tindak_lanjut)));
        if (! $hasTinjut) {
            return response()->json([
                'message' => 'Auditee belum mengisi tindak lanjut, sehingga Uji Nilai belum dapat dilakukan.',
                'errors' => [
                    'kesimpulan_tinjut' => ['Auditee belum mengisi tindak lanjut.'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'kesimpulan_tinjut' => ['required', 'string', 'max:100'],
            'alasan_tinjut' => ['nullable', 'string', 'max:2000'],
            'status_tinjut' => ['required', 'string', Rule::in(KkaTabulasi::STATUS_TINJUTS)],
        ], [
            'kesimpulan_tinjut.required' => 'Kesimpulan wajib diisi.',
            'status_tinjut.required' => 'Status tindak lanjut wajib dipilih.',
        ]);

        $tabulasi->update([
            'kesimpulan_tinjut' => $validated['kesimpulan_tinjut'],
            'alasan_tinjut' => $validated['alasan_tinjut'] ?? null,
            'status_tinjut' => $validated['status_tinjut'],
        ]);

        return response()->json([
            'message' => 'Hasil Uji Nilai berhasil disimpan.',
            'data' => $this->reloadAndFormat($tabulasi),
        ]);
    }

    /**
     * Kirim pesan pada Konversasi Tanggapan (teks dan/atau file).
     */
    public function storeMessage(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk konversasi ini.',
            ], 403);
        }

        $validated = $request->validate([
            'pesan' => ['nullable', 'string', 'max:2000', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:10240', 'required_without:pesan'],
        ], [
            'pesan.required_without' => 'Ketikkan pesan atau lampirkan file.',
            'file.required_without' => 'Ketikkan pesan atau lampirkan file.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('kka/tindak-lanjut/chat', 'public');
            $fileName = $file->getClientOriginalName();
        }

        $tabulasi->messages()->create([
            'user_id' => $request->user()?->id,
            'pesan' => isset($validated['pesan']) ? trim((string) $validated['pesan']) : null,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'data' => $this->reloadAndFormat($tabulasi),
        ], 201);
    }

    /**
     * Simpan Lampiran Tindak Lanjut (Upload File atau Tautan / Link URL + Keterangan Lampiran).
     */
    public function storeLampiran(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah lampiran ini.',
            ], 403);
        }

        $jenisUnggahan = (string) $request->input('jenis_unggahan', 'file');

        $validated = $request->validate([
            'jenis_unggahan' => ['nullable', 'string', Rule::in(['file', 'link'])],
            'lampiran' => [
                Rule::requiredIf($jenisUnggahan === 'file'),
                'nullable',
                'file',
                'max:10240',
            ],
            'link_url' => [
                Rule::requiredIf($jenisUnggahan === 'link'),
                'nullable',
                'url',
                'max:1000',
            ],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'lampiran.required' => 'Silakan pilih file lampiran pendukung terlebih dahulu.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 10 MB.',
            'link_url.required' => 'Tautan / Link URL wajib diisi.',
            'link_url.url' => 'Format Tautan / Link URL tidak valid.',
        ]);

        $path = null;
        $name = null;
        $linkUrl = null;

        if ($jenisUnggahan === 'link') {
            $linkUrl = trim((string) $validated['link_url']);
            $name = $linkUrl;
        } elseif ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $path = $file->store('kka/tindak-lanjut', 'public');
            $name = $file->getClientOriginalName();

            $tabulasi->update([
                'lampiran_tinjut_path' => $path,
                'lampiran_tinjut_name' => $name,
            ]);
        }

        $tabulasi->lampirans()->create([
            'user_id' => $request->user()?->id,
            'jenis_unggahan' => $jenisUnggahan,
            'file_path' => $path,
            'file_name' => $name,
            'link_url' => $linkUrl,
            'keterangan' => ! empty($validated['keterangan']) ? trim((string) $validated['keterangan']) : ($name ?? 'Lampiran Pendukung'),
        ]);

        return response()->json([
            'message' => 'Lampiran pendukung berhasil dikirim.',
            'data' => $this->reloadAndFormat($tabulasi),
        ]);
    }

    public function print(Request $request, Kka $kka): View
    {
        if (! $this->canAccessKka($request->user(), $kka)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak PTL ini.');
        }

        $kka->load([
            'pka.sprin',
            'tabulasis' => fn ($q) => $q->orderBy('jenis_tabulasi')->orderBy('nomor'),
            'tabulasis.kodeTemuan:id,kode,nama',
            'tabulasis.subKodeTemuan:id,kode_id,kode,nama',
            'tabulasis.kodeTinjut:id,kode,nama',
            'tabulasis.subKodeTinjut:id,kode_id,kode,nama',
        ]);

        return view('audit.tindak-lanjut.print', [
            'kka' => $kka,
            'pka' => $kka->pka,
            'sprin' => $kka->pka?->sprin,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function reloadAndFormat(KkaTabulasi $tabulasi): array
    {
        $tabulasi->load([
            'kodeTemuan:id,kode,nama',
            'subKodeTemuan:id,kode_id,kode,nama',
            'kodeTinjut:id,kode,nama',
            'subKodeTinjut:id,kode_id,kode,nama',
            'messages.user:id,name,role',
            'lampirans.user:id,name,role',
        ]);

        return $this->formatTabulasiRow($tabulasi);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatTabulasiRow(KkaTabulasi $t): array
    {
        $hasTinjut = ! empty(trim(strip_tags((string) $t->tindak_lanjut)));

        return [
            'id' => $t->id,
            'jenis_tabulasi' => $t->jenis_tabulasi ?? 'Temuan',
            'nomor' => $t->nomor ?? '-',
            'tipe_temuan' => $t->tipe_temuan ?? '',
            'kode_temuan_kode' => $t->kodeTemuan?->kode,
            'sub_kode_temuan_kode' => $t->subKodeTemuan?->kode,
            'nilai_temuan' => $t->nilai_temuan !== null ? (float) $t->nilai_temuan : null,
            'nilai_temuan_formatted' => $t->nilai_temuan !== null
                ? 'IDR '.number_format((float) $t->nilai_temuan, 0, ',', '.')
                : '-',
            'uraian_temuan' => $t->uraian_temuan ?? '',
            'uraian_kriteria' => $t->uraian_kriteria ?? '',
            'uraian_sebab' => $t->uraian_sebab ?? '',
            'uraian_akibat' => $t->uraian_akibat ?? '',
            'uraian_rekomendasi' => $t->uraian_rekomendasi ?? '',
            'status_tinjut' => $t->status_tinjut ?: 'Belum Ditinjut',
            'has_tinjut' => $hasTinjut,
            'kode_tinjut_id' => $t->kode_tinjut_id ? (string) $t->kode_tinjut_id : '',
            'sub_kode_tinjut_id' => $t->sub_kode_tinjut_id ? (string) $t->sub_kode_tinjut_id : '',
            'kode_tinjut_kode' => $t->kodeTinjut?->kode,
            'sub_kode_tinjut_kode' => $t->subKodeTinjut?->kode,
            'tindak_lanjut' => $t->tindak_lanjut ?? '',
            'nilai_pengembalian' => $t->nilai_pengembalian !== null ? (string) (float) $t->nilai_pengembalian : '',
            'nilai_pengembalian_formatted' => $t->nilai_pengembalian !== null
                ? 'IDR '.number_format((float) $t->nilai_pengembalian, 0, ',', '.')
                : null,
            'kesimpulan_tinjut' => $t->kesimpulan_tinjut ?? '',
            'alasan_tinjut' => $t->alasan_tinjut ?? '',
            'lampiran_tinjut_name' => $t->lampiran_tinjut_name,
            'lampiran_tinjut_url' => $t->lampiran_tinjut_path ? asset('storage/'.$t->lampiran_tinjut_path) : null,
            'messages' => ($t->relationLoaded('messages') ? $t->messages : collect())->map(
                fn (KkaTabulasiMessage $m): array => [
                    'id' => $m->id,
                    'sender_name' => $m->user?->name ?? 'Pengguna',
                    'sender_role' => $m->user?->role ?? '',
                    'pesan' => $m->pesan,
                    'file_name' => $m->file_name,
                    'file_url' => $m->file_path ? asset('storage/'.$m->file_path) : null,
                    'time_ago' => $m->created_at?->locale('id')->diffForHumans() ?? 'Baru saja',
                ]
            )->values()->all(),
            'lampirans' => ($t->relationLoaded('lampirans') ? $t->lampirans : collect())->map(
                fn (KkaTabulasiLampiran $l): array => [
                    'id' => $l->id,
                    'jenis_unggahan' => $l->jenis_unggahan,
                    'file_name' => $l->file_name,
                    'url' => $l->jenis_unggahan === 'link'
                        ? $l->link_url
                        : ($l->file_path ? asset('storage/'.$l->file_path) : null),
                    'keterangan' => $l->keterangan,
                    'uploader_name' => $l->user?->name ?? '-',
                    'created_at' => $l->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-',
                ]
            )->values()->all(),
        ];
    }

    protected function resolveAuditeeName(Kka $kka): string
    {
        if ($kka->auditee_level === 'Nasional') {
            return (string) ($kka->auditeePlotting?->plot ?? '-');
        }

        if ($kka->auditee_kode_wilayah) {
            $wilayah = DB::table('wilayah')
                ->where('kode_wilayah', $kka->auditee_kode_wilayah)
                ->whereNull('deleted_at')
                ->first();
            if ($wilayah) {
                return (string) ($wilayah->desa_kelurahan
                    ?? $wilayah->kecamatan
                    ?? $wilayah->kabupaten_kota
                    ?? $wilayah->provinsi
                    ?? '-');
            }
        }

        return '-';
    }

    protected function isAuditorOrReviewer(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->role !== 'Auditee';
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

        return Kka::query()->whereKey($kka->id)->visibleTo($user)->exists();
    }
}
