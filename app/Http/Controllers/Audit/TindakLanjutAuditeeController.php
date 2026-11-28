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
            'auditeePlotting:id,nama_unit',
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
                'isAuditee' => $authUser?->role === 'Auditee',
                'currentUserId' => $authUser?->id,
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

        if ($tabulasi->status_tinjut === 'Selesai') {
            return response()->json([
                'message' => 'Tindak lanjut sudah selesai diverifikasi oleh auditor dan tidak dapat diubah lagi.',
            ], 422);
        }

        if ($request->has('nilai_pengembalian') && is_string($request->input('nilai_pengembalian'))) {
            $cleaned = preg_replace('/[^\d]/', '', (string) $request->input('nilai_pengembalian'));
            $request->merge([
                'nilai_pengembalian' => $cleaned !== '' ? $cleaned : null,
            ]);
        }

        $validated = $request->validate([
            'status_tinjut' => ['nullable', 'string', Rule::in(array_merge(KkaTabulasi::STATUS_TINJUTS, ['Dalam Proses', 'Belum Sesuai']))],
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
        if (! $nextStatus || $nextStatus === 'Belum Ditinjut' || $nextStatus === 'Dalam Proses') {
            $nextStatus = 'Sudah Ditinjut';
        } elseif ($nextStatus === 'Belum Sesuai') {
            $nextStatus = 'Koreksi';
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

        if (empty(trim(strip_tags((string) $request->input('kesimpulan_tinjut'))))) {
            return response()->json([
                'message' => 'Kesimpulan wajib diisi.',
                'errors' => [
                    'kesimpulan_tinjut' => ['Kesimpulan wajib diisi.'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'kesimpulan_tinjut' => ['required', 'string'],
            'alasan_tinjut' => ['nullable', 'string'],
            'status_tinjut' => ['required', 'string', Rule::in(array_merge(KkaTabulasi::STATUS_TINJUTS, ['Dalam Proses', 'Belum Sesuai']))],
        ], [
            'kesimpulan_tinjut.required' => 'Kesimpulan wajib diisi.',
            'status_tinjut.required' => 'Status tindak lanjut wajib dipilih.',
        ]);

        $statusTinjut = match ($validated['status_tinjut']) {
            'Dalam Proses' => 'Sudah Ditinjut',
            'Belum Sesuai' => 'Koreksi',
            default => $validated['status_tinjut'],
        };

        $tabulasi->update([
            'kesimpulan_tinjut' => $validated['kesimpulan_tinjut'],
            'alasan_tinjut' => $validated['alasan_tinjut'] ?? null,
            'status_tinjut' => $statusTinjut,
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
     * Simpan Lampiran Tindak Lanjut (Upload File tunggal / multi-file atau Tautan / Link URL + Keterangan Lampiran).
     */
    public function storeLampiran(Request $request, Kka $kka, KkaTabulasi $tabulasi): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah lampiran ini.',
            ], 403);
        }

        $jenisUnggahan = (string) $request->input('jenis_unggahan', 'file');

        if ($jenisUnggahan === 'link') {
            $validated = $request->validate([
                'link_url' => ['required', 'url', 'max:1000'],
                'keterangan' => ['nullable', 'string', 'max:500'],
            ], [
                'link_url.required' => 'Tautan / Link URL wajib diisi.',
                'link_url.url' => 'Format Tautan / Link URL tidak valid.',
            ]);

            $linkUrl = trim((string) $validated['link_url']);
            $keterangan = ! empty($validated['keterangan']) ? trim((string) $validated['keterangan']) : 'Link Google Drive';

            $tabulasi->lampirans()->create([
                'user_id' => $request->user()?->id,
                'jenis_unggahan' => 'link',
                'file_path' => null,
                'file_name' => $linkUrl,
                'link_url' => $linkUrl,
                'keterangan' => $keterangan,
            ]);

            return response()->json([
                'message' => 'Tautan lampiran berhasil ditambahkan.',
                'data' => $this->reloadAndFormat($tabulasi),
            ]);
        }

        // Mode Upload File (Mendukung Multi-file)
        $files = [];
        if ($request->hasFile('lampiran')) {
            $uploaded = $request->file('lampiran');
            $files = is_array($uploaded) ? $uploaded : [$uploaded];
        } elseif ($request->hasFile('files')) {
            $uploaded = $request->file('files');
            $files = is_array($uploaded) ? $uploaded : [$uploaded];
        }

        if (empty($files)) {
            return response()->json([
                'message' => 'Silakan pilih minimal satu file lampiran pendukung.',
                'errors' => [
                    'lampiran' => ['Silakan pilih minimal satu file lampiran pendukung.'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'lampiran' => ['nullable'],
            'lampiran.*' => ['file', 'max:10240'],
            'files.*' => ['file', 'max:10240'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'lampiran.*.max' => 'Ukuran setiap file lampiran maksimal 10 MB.',
            'files.*.max' => 'Ukuran setiap file lampiran maksimal 10 MB.',
        ]);

        $customKet = ! empty($validated['keterangan']) ? trim((string) $validated['keterangan']) : null;
        $lastPath = null;
        $lastName = null;

        foreach ($files as $file) {
            $path = $file->store('kka/tindak-lanjut', 'public');
            $name = $file->getClientOriginalName();
            $lastPath = $path;
            $lastName = $name;

            $tabulasi->lampirans()->create([
                'user_id' => $request->user()?->id,
                'jenis_unggahan' => 'file',
                'file_path' => $path,
                'file_name' => $name,
                'link_url' => null,
                'keterangan' => $customKet ?? $name,
            ]);
        }

        if ($lastPath) {
            $tabulasi->update([
                'lampiran_tinjut_path' => $lastPath,
                'lampiran_tinjut_name' => $lastName,
            ]);
        }

        return response()->json([
            'message' => count($files).' file lampiran pendukung berhasil diunggah.',
            'data' => $this->reloadAndFormat($tabulasi),
        ]);
    }

    /**
     * Hapus lampiran pendukung tindak lanjut.
     */
    public function destroyLampiran(Request $request, Kka $kka, KkaTabulasi $tabulasi, KkaTabulasiLampiran $lampiran): JsonResponse
    {
        if (! $this->canAccessKka($request->user(), $kka) || (int) $tabulasi->kka_id !== (int) $kka->id || (int) $lampiran->kka_tabulasi_id !== (int) $tabulasi->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk menghapus lampiran ini.',
            ], 403);
        }

        if ($lampiran->file_path && Storage::disk('public')->exists($lampiran->file_path)) {
            Storage::disk('public')->delete($lampiran->file_path);
        }

        $lampiran->delete();

        $latest = $tabulasi->lampirans()->whereNotNull('file_path')->latest('id')->first();
        $tabulasi->update([
            'lampiran_tinjut_path' => $latest?->file_path,
            'lampiran_tinjut_name' => $latest?->file_name,
        ]);

        return response()->json([
            'message' => 'Lampiran berhasil dihapus.',
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
            'kode_tinjut_nama' => $t->kodeTinjut?->nama,
            'sub_kode_tinjut_kode' => $t->subKodeTinjut?->kode,
            'sub_kode_tinjut_nama' => $t->subKodeTinjut?->nama,
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
                    'user_id' => $m->user_id,
                    'is_current_user' => (int) $m->user_id === (int) auth()->id(),
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
                    'uploader_name' => $l->user?->name ?? 'Pengguna',
                    'created_at' => $l->created_at?->locale('id')->timezone('Asia/Jakarta')->translatedFormat('l, j F Y \p\u\k\u\l H.i') ?? '-',
                ]
            )->values()->all(),
        ];
    }

    protected function resolveAuditeeName(Kka $kka): string
    {
        if ($kka->auditee_perangkat_daerah_id) {
            return (string) ($kka->auditeePerangkatDaerah?->nama_unit ?? '-');
        }

        if (in_array($kka->auditee_level, ['Nasional', 'Dinas'], true)) {
            return (string) ($kka->auditeePerangkatDaerah?->nama_unit ?? '-');
        }

        if ($kka->auditee_kode_wilayah) {
            $wilayah = DB::table('struktur_perangkat_daerah')
                ->where('kode_wilayah', $kka->auditee_kode_wilayah)
                ->whereNull('deleted_at')
                ->first();
            if ($wilayah) {
                return (string) ($wilayah->nama_unit
                    ?? $wilayah->desa_kelurahan
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

        return ! in_array($user->role, ['Auditee', 'Admin Dinas', 'Admin Kecamatan'], true);
    }

    protected function canAccessKka(?User $user, Kka $kka): bool
    {
        if (! $user) {
            return false;
        }

        return Kka::query()->whereKey($kka->id)->visibleForTindakLanjut($user)->exists();
    }
}
