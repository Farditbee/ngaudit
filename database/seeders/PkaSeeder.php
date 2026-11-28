<?php

namespace Database\Seeders;

use App\Models\Pka;
use App\Models\PkaLangkahKerja;
use App\Models\PkaReview;
use App\Models\Sprin;
use App\Models\TemplatePka;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PkaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approvedSprins = Sprin::query()
            ->approved()
            ->with([
                'tim.auditors',
                'tim.strukturPerangkatDaerah',
            ])
            ->orderBy('id')
            ->get();

        if ($approvedSprins->isEmpty()) {
            $this->command?->info('Tidak ada Sprin yang disetujui untuk di-seed PKA-nya.');

            return;
        }

        $templates = TemplatePka::query()
            ->where('lingkup', 'Dinas')
            ->get();

        // Bersihkan data PKA lama dari Sprin yang disetujui agar seeder bersifat idempoten
        $existingPkaIds = Pka::query()
            ->whereIn('sprin_id', $approvedSprins->pluck('id'))
            ->pluck('id');

        if ($existingPkaIds->isNotEmpty()) {
            PkaReview::query()->whereIn('pka_id', $existingPkaIds)->forceDelete();
            PkaLangkahKerja::query()->whereIn('pka_id', $existingPkaIds)->forceDelete();
            Pka::query()->whereIn('id', $existingPkaIds)->forceDelete();
        }

        $pkaCounter = 1;

        $opdTopics = [
            'DINKES' => [
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengelolaan dan Pemanfaatan Dana Bantuan Operasional Kesehatan (BOK) Puskesmas',
                    'anggaran' => 125000000,
                ],
                [
                    'bidang' => 'Logistik',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengadaan dan Distribusi Obat-obatan serta Alat Kesehatan Esensial pada Instalasi Farmasi',
                    'anggaran' => 210000000,
                ],
                [
                    'bidang' => 'Manajemen Anggaran dan Keuangan',
                    'jenis_audit' => 'Audit PNBP',
                    'sasaran_auditi' => 'Tata Kelola Keuangan dan Penerimaan Klaim Pelayanan Kesehatan Pasien BPJS',
                    'anggaran' => 175000000,
                ],
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pelaksanaan Program Percepatan Penurunan Stunting dan Pemberian Makanan Tambahan (PMT)',
                    'anggaran' => 95000000,
                ],
                [
                    'bidang' => 'Investigasi',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Pemeriksaan Kepatuhan Penatausahaan dan Inventarisasi Aset Medis Puskesmas Keliling',
                    'anggaran' => 80000000,
                ],
            ],
            'DISDIKBUD' => [
                [
                    'bidang' => 'Manajemen Anggaran dan Keuangan',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengelolaan dan Pertanggungjawaban Dana Bantuan Operasional Satuan Pendidikan (BOSP)',
                    'anggaran' => 250000000,
                ],
                [
                    'bidang' => 'Logistik',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengadaan Perangkat TIK Chromebook dan Media Pembelajaran Digital Sekolah Dasar',
                    'anggaran' => 180000000,
                ],
                [
                    'bidang' => 'Sumber Daya Manusia',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Akuntabilitas Penyaluran Tunjangan Profesi Guru (TPG) dan Tambahan Penghasilan Guru ASN',
                    'anggaran' => 140000000,
                ],
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Pelaksanaan Program Rehabilitasi Ruang Kelas Rusak dan Sarana Sanitasi Satuan Pendidikan',
                    'anggaran' => 290000000,
                ],
                [
                    'bidang' => 'Investigasi',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Audit Kepatuhan Pelaksanaan Pengadaan Jasa Konsultansi Perencanaan dan Pengawasan Gedung Sekolah',
                    'anggaran' => 95000000,
                ],
            ],
            'DBMBK' => [
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pelaksanaan Pekerjaan Preservasi dan Rekonstruksi Jalan Poros Penghubung Antar Kecamatan',
                    'anggaran' => 380000000,
                ],
                [
                    'bidang' => 'Logistik',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengujian Mutu Material dan Uji Petik Ketebalan Campuran Aspal Panas (AC-WC) Jalan Kabupaten',
                    'anggaran' => 220000000,
                ],
                [
                    'bidang' => 'Manajemen Anggaran dan Keuangan',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Pemeriksaan Kontrak dan Pembayaran Termin Pembangunan Jembatan Strategis Ruas Kabupaten',
                    'anggaran' => 310000000,
                ],
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengadaan Barang/Jasa Konstruksi Pembangunan Saluran Drainase dan Trotoar Kawasan Perkotaan',
                    'anggaran' => 165000000,
                ],
                [
                    'bidang' => 'Investigasi',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Pemeriksaan Kelaikan Operasional dan Efektivitas Pemeliharaan Alat Berat UPT Workshop',
                    'anggaran' => 115000000,
                ],
            ],
            'DLH' => [
                [
                    'bidang' => 'Manajemen Anggaran dan Keuangan',
                    'jenis_audit' => 'Audit PNBP',
                    'sasaran_auditi' => 'Akuntabilitas Pemungutan dan Penyetoran Retribusi Pelayanan Persampahan dan Kebersihan',
                    'anggaran' => 135000000,
                ],
                [
                    'bidang' => 'Logistik',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengelolaan Operasional, Bahan Bakar, dan Pemeliharaan Rutin Armada Truk Pengangkut Sampah',
                    'anggaran' => 190000000,
                ],
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => 'Pengawasan Ketaatan Pengelolaan Limbah Bahan Berbahaya dan Beracun (B3) Fasilitas Pelayanan',
                    'anggaran' => 110000000,
                ],
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Pengendalian Pencemaran Lingkungan Hidup dan Pemantauan Berkala Kualitas Air Sungai',
                    'anggaran' => 145000000,
                ],
                [
                    'bidang' => 'Investigasi',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => 'Audit Khusus Pengelolaan dan Pemanfaatan Lahan Tempat Pemrosesan Akhir (TPA) Sampah',
                    'anggaran' => 170000000,
                ],
            ],
        ];

        $langkahKerjaItems = [
            [
                'uraian' => '<p>Melakukan pengujian substantif atas kelengkapan, keabsahan, dan kesesuaian dokumen bukti pertanggungjawaban belanja terhadap ketentuan peraturan perundang-undangan.</p>',
                'waktu_rencana' => 16,
                'waktu_realisasi' => 16,
                'catatan' => 'Verifikasi bukti SPJ, kuitansi, faktur pajak, dan berita acara serah terima.',
            ],
            [
                'uraian' => '<p>Melakukan pemeriksaan fisik lapangan / uji petik bersama penanggung jawab teknis serta memastikan kesesuaian volume dan spesifikasi hasil pengadaan/pekerjaan.</p>',
                'waktu_rencana' => 24,
                'waktu_realisasi' => 24,
                'catatan' => 'Cek fisik lokasi pekerjaan dan dokumentasi foto kondisi eksisting.',
            ],
            [
                'uraian' => '<p>Melakukan wawancara dan konfirmasi kepada pihak pengelola kegiatan, bendahara, serta pihak ketiga untuk mendalami indikasi kendala pelaksanaan dan pengendalian internal.</p>',
                'waktu_rencana' => 12,
                'waktu_realisasi' => 12,
                'catatan' => 'Konfirmasi berita acara wawancara dengan PPTK dan pengurus barang.',
            ],
            [
                'uraian' => '<p>Menganalisis sistem pengendalian intern, menyusun kertas kerja audit (KKA), serta merumuskan simpulan sementara dan rekomendasi perbaikan yang bernilai tambah.</p>',
                'waktu_rencana' => 18,
                'waktu_realisasi' => 18,
                'catatan' => 'Penyusunan KKA dan perumusan matriks temuan pemeriksaan.',
            ],
        ];

        foreach ($approvedSprins as $sprin) {
            $opdName = $sprin->tim?->strukturPerangkatDaerah?->nama_unit ?? 'Dinas';

            $opdCode = match (true) {
                str_contains($opdName, 'Kesehatan') => 'DINKES',
                str_contains($opdName, 'Pendidikan') => 'DISDIKBUD',
                str_contains($opdName, 'Bina Marga') => 'DBMBK',
                str_contains($opdName, 'Lingkungan Hidup') => 'DLH',
                default => 'DINAS',
            };

            $pool = $opdTopics[$opdCode] ?? [
                [
                    'bidang' => 'Operasional',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => "Evaluasi Kinerja Pelaksanaan Program Kerja dan Anggaran Operasional pada {$opdName}",
                    'anggaran' => 150000000,
                ],
                [
                    'bidang' => 'Manajemen Anggaran dan Keuangan',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => "Pemeriksaan Akuntabilitas Pengelolaan Kas dan Belanja Barang/Jasa pada {$opdName}",
                    'anggaran' => 200000000,
                ],
                [
                    'bidang' => 'Logistik',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => "Pengujian Penatausahaan dan Inventarisasi Barang Milik Daerah pada {$opdName}",
                    'anggaran' => 130000000,
                ],
                [
                    'bidang' => 'Investigasi',
                    'jenis_audit' => 'Audit Tujuan Tertentu',
                    'sasaran_auditi' => "Audit Kepatuhan Pelaksanaan Pengadaan Langsung dan Kontrak Kerja pada {$opdName}",
                    'anggaran' => 110000000,
                ],
                [
                    'bidang' => 'Sumber Daya Manusia',
                    'jenis_audit' => 'Audit Kinerja',
                    'sasaran_auditi' => "Evaluasi Efektivitas Manajemen Kepegawaian dan Disiplin Kerja ASN pada {$opdName}",
                    'anggaran' => 90000000,
                ],
            ];

            // Acak jumlah PKA antara 2 sampai 5 per Sprin
            $count = random_int(2, 5);

            // Ambil auditor dari tim Sprin
            $auditors = $sprin->tim?->auditors ?? collect();
            $auditorIds = $auditors->pluck('id')->all();

            $sprinStartDate = $sprin->tgl_mulai ? Carbon::parse($sprin->tgl_mulai) : Carbon::now();

            for ($i = 0; $i < $count; $i++) {
                $topic = $pool[$i % count($pool)];

                // Tentukan status: mayoritas Final agar bisa dipakai buat KKA, elemen terakhir jika count > 2 bisa Draft
                $isFinal = ($i < $count - 1) || ($count === 2 && $i === 0);
                $status = $isFinal ? 'Final' : 'Draft';

                $tanggalPka = $sprinStartDate->copy()->subDays(random_int(1, 4))->toDateString();

                // Pilih pembuat PKA dari anggota auditor tim secara bergantian / acak
                $dibuatOlehId = ! empty($auditorIds)
                    ? $auditorIds[$i % count($auditorIds)]
                    : $sprin->katim;

                $direviuOlehId = $sprin->dalnis ?: $sprin->daltu;

                // Template PKA pendukung
                $template = $templates->isNotEmpty()
                    ? $templates->get($i % $templates->count())
                    : null;

                $nomorPka = sprintf(
                    'PKA/%03d/%s/ITDA-LT/%s/2026',
                    $pkaCounter++,
                    $opdCode,
                    $sprin->tahap ?: 'I'
                );

                $approvedDalnisDate = $isFinal
                    ? Carbon::parse($tanggalPka)->addDay()->setTime(10, 0, 0)
                    : null;

                $approvedDaltuDate = $isFinal
                    ? Carbon::parse($tanggalPka)->addDays(2)->setTime(14, 30, 0)
                    : null;

                $pendahuluanHtml = $template?->pendahuluan ?: sprintf(
                    '<p>Program Kerja Audit (PKA) ini disusun sebagai pedoman kerja terstruktur bagi Tim Audit Inspektorat Kabupaten Lampung Tengah dalam melaksanakan penugasan pengawasan intern pada <strong>%s</strong> berdasarkan Surat Perintah Tugas Nomor <strong>%s</strong>.</p><ul><li>Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah.</li><li>Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP).</li><li>Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.</li></ul>',
                    e($opdName),
                    e($sprin->no_sprin)
                );

                $tujuanHtml = $template?->tujuan ?: sprintf(
                    '<p>Tujuan pelaksanaan audit ini adalah untuk:</p><ol><li>Menilai kepatuhan pelaksanaan program dan kegiatan pada <strong>%s</strong> terhadap peraturan perundang-undangan yang berlaku.</li><li>Menguji efisiensi, efektivitas, dan kehematan (3E) dalam penggunaan anggaran daerah.</li><li>Memberikan rekomendasi konstruktif dan bernilai tambah guna perbaikan tata kelola serta mitigasi risiko.</li></ol>',
                    e($opdName)
                );

                $ruangLingkupHtml = $template?->ruang_lingkup ?: sprintf(
                    '<p>Ruang lingkup audit meliputi pengujian dokumen pertanggungjawaban, pemeriksaan fisik lapangan, wawancara, dan analisis sistem pengendalian intern atas <em>%s</em> pada <strong>%s</strong> Tahun Anggaran 2025.</p>',
                    e($topic['sasaran_auditi']),
                    e($opdName)
                );

                $sasaranHtml = $template?->sasaran ?: sprintf(
                    '<p>Sasaran audit yang hendak dicapai meliputi:</p><ul><li>Tercapainya akuntabilitas dan ketepatan sasaran belanja daerah pada %s.</li><li>Tertib administrasi dalam penatausahaan keuangan dan aset daerah.</li><li>Tersedianya rekomendasi strategis penyelesaian kendala operasional.</li></ul>',
                    e($opdName)
                );

                $waktuPelaksanaanHtml = sprintf(
                    '<p>Audit dilaksanakan selama %d hari kalender, terhitung mulai tanggal %s sampai dengan %s sesuai dengan jadwal penugasan pada Surat Perintah Tugas.</p>',
                    $sprin->tgl_mulai && $sprin->tgl_akhir ? Carbon::parse($sprin->tgl_mulai)->diffInDays(Carbon::parse($sprin->tgl_akhir)) + 1 : 15,
                    $sprin->tgl_mulai ? Carbon::parse($sprin->tgl_mulai)->translatedFormat('d F Y') : '-',
                    $sprin->tgl_akhir ? Carbon::parse($sprin->tgl_akhir)->translatedFormat('d F Y') : '-'
                );

                $periodeHtml = '<p>Tahun Anggaran 2025 (Periode 1 Januari 2025 s.d. 31 Desember 2025).</p>';

                $penutupHtml = '<p>Demikian Program Kerja Audit (PKA) ini disusun untuk dipedomani oleh seluruh anggota tim pemeriksa dalam menjalankan penugasan audit secara profesional, objektif, dan independen.</p>';

                $pka = Pka::query()->create([
                    'sprin_id' => $sprin->id,
                    'nomor_pka' => $nomorPka,
                    'tanggal' => $tanggalPka,
                    'anggaran' => $topic['anggaran'],
                    'bidang' => $topic['bidang'],
                    'jenis_audit' => $topic['jenis_audit'],
                    'sasaran_auditi' => $topic['sasaran_auditi'],
                    'status' => $status,
                    'approved_by_dalnis_date' => $approvedDalnisDate,
                    'approved_by_daltu_date' => $approvedDaltuDate,
                    'dibuat_oleh_id' => $dibuatOlehId,
                    'direviu_oleh_id' => $direviuOlehId,
                    'template_pka_id' => $template?->id,
                    'pendahuluan' => $pendahuluanHtml,
                    'tujuan' => $tujuanHtml,
                    'ruang_lingkup' => $ruangLingkupHtml,
                    'sasaran' => $sasaranHtml,
                    'waktu_pelaksanaan' => $waktuPelaksanaanHtml,
                    'periode' => $periodeHtml,
                    'penutup' => $penutupHtml,
                    'created_by' => $dibuatOlehId,
                ]);

                // Tambahkan Review untuk PKA Final
                if ($isFinal) {
                    if ($sprin->dalnis) {
                        PkaReview::query()->create([
                            'pka_id' => $pka->id,
                            'user_id' => $sprin->dalnis,
                            'role_type' => PkaReview::TYPE_DALNIS,
                            'status' => PkaReview::STATUS_DISETUJUI,
                            'catatan' => 'PKA telah ditelaah dan substansi langkah kerja telah sesuai dengan tujuan penugasan audit. Disetujui.',
                            'created_at' => $approvedDalnisDate,
                            'updated_at' => $approvedDalnisDate,
                        ]);
                    }

                    if ($sprin->daltu) {
                        PkaReview::query()->create([
                            'pka_id' => $pka->id,
                            'user_id' => $sprin->daltu,
                            'role_type' => PkaReview::TYPE_DALTU,
                            'status' => PkaReview::STATUS_DISETUJUI,
                            'catatan' => 'PKA memenuhi standar kendali mutu pengawasan intern. Disetujui untuk dilaksanakan.',
                            'created_at' => $approvedDaltuDate,
                            'updated_at' => $approvedDaltuDate,
                        ]);
                    }
                }

                // Tambahkan 3 sampai 4 langkah kerja untuk setiap PKA
                $numLangkahKerja = random_int(3, 4);
                for ($k = 0; $k < $numLangkahKerja; $k++) {
                    $item = $langkahKerjaItems[$k % count($langkahKerjaItems)];

                    $auditorRencanaId = ! empty($auditorIds)
                        ? $auditorIds[($i + $k) % count($auditorIds)]
                        : $dibuatOlehId;

                    $auditorRealisasiId = $isFinal ? $auditorRencanaId : null;
                    $waktuRealisasi = $isFinal ? $item['waktu_realisasi'] : null;

                    PkaLangkahKerja::query()->create([
                        'pka_id' => $pka->id,
                        'bidang' => $pka->bidang,
                        'uraian' => $item['uraian'],
                        'dilaksanakan_rencana_id' => $auditorRencanaId,
                        'dilaksanakan_realisasi_id' => $auditorRealisasiId,
                        'waktu_rencana' => $item['waktu_rencana'],
                        'waktu_realisasi' => $waktuRealisasi,
                        'catatan' => $item['catatan'],
                        'created_by' => $dibuatOlehId,
                    ]);
                }
            }
        }

        $this->command?->info("Berhasil membuat PKA seeder untuk {$approvedSprins->count()} Sprin yang disetujui (total {$pkaCounter} PKA diproses).");
    }
}
