<?php

namespace Database\Seeders;

use App\Models\Kka;
use App\Models\KkaTabulasi;
use App\Models\Pka;
use App\Models\Sprin;
use App\Models\StrukturPerangkatDaerah;
use App\Models\TemplateKka;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnevDummyTemuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('role', 'Super Admin')->first() ?? User::query()->first();
        $userId = $user ? $user->id : 1;

        // Bersihkan data temuan dan KKA dummy anev sebelumnya agar idempoten
        DB::table('kka_tabulasis')->where('jenis_tabulasi', 'Temuan')->delete();
        $oldKkaIds = DB::table('kkas')->where('nomor_kka', 'like', 'KKA/ANEV/%')->pluck('id');
        if ($oldKkaIds->isNotEmpty()) {
            DB::table('kka_tabulasis')->whereIn('kka_id', $oldKkaIds)->delete();
            DB::table('kkas')->whereIn('id', $oldKkaIds)->delete();
        }
        $oldPkaIds = DB::table('pkas')->where('nomor_pka', 'like', 'PKA/ANEV/%')->pluck('id');
        if ($oldPkaIds->isNotEmpty()) {
            DB::table('pkas')->whereIn('id', $oldPkaIds)->delete();
        }

        // 1. Definisi Satker Dinas & Kecamatan untuk Tahap 1
        $dinasList = [
            ['id' => 51, 'tim_id' => 2, 'alias' => 'DINKES'],
            ['id' => 37, 'tim_id' => 3, 'alias' => 'DISDIKBUD'],
            ['id' => 60, 'tim_id' => 4, 'alias' => 'DBMBK'],
            ['id' => 165, 'tim_id' => 7, 'alias' => 'DISHUB'],
            ['id' => 257, 'tim_id' => 8, 'alias' => 'BPKAD'],
        ];

        $kecamatanList = [
            ['id' => 320, 'tim_id' => 9, 'alias' => 'KEC-GS'],
            ['id' => 329, 'tim_id' => 10, 'alias' => 'KEC-TB'],
            ['id' => 338, 'tim_id' => 11, 'alias' => 'KEC-SA'],
            ['id' => 347, 'tim_id' => 12, 'alias' => 'KEC-BRN'],
            ['id' => 356, 'tim_id' => 13, 'alias' => 'KEC-BK'],
            ['id' => 374, 'tim_id' => 14, 'alias' => 'KEC-KL'],
        ];

        // 2. Setup Sprins Tahap 1
        $sprinMap = [];

        foreach ($dinasList as $item) {
            $sprin = Sprin::query()
                ->where('sprin_tipe', 'Dinas')
                ->where('tahap', '1')
                ->where('struktur_perangkat_daerah_id', $item['id'])
                ->first();

            if (! $sprin) {
                $sprin = Sprin::query()->create([
                    'user_id' => $userId,
                    'no_sprin' => "SPRIN-ANEV/DINAS/{$item['alias']}/I/2026",
                    'no_surat_pemberitahuan' => "SP-ANEV/DINAS/{$item['alias']}/I/2026",
                    'tgl_mulai' => '2026-01-10',
                    'tgl_akhir' => '2026-01-29',
                    'tim_id' => $item['tim_id'],
                    'katim' => 48,
                    'dalnis' => 40,
                    'daltu' => 33,
                    'tahap' => '1',
                    'struktur_perangkat_daerah_id' => $item['id'],
                    'sprin_tipe' => 'Dinas',
                    'keterangan' => 'Pengawasan dan Evaluasi Tabulasi Anev Tahap I Tahun 2026',
                    'approved_by_daltu_date' => '2026-01-08 10:00:00',
                    'created_by' => $userId,
                ]);
            }
            $sprinMap["Dinas_{$item['id']}"] = $sprin;
        }

        foreach ($kecamatanList as $item) {
            $sprin = Sprin::query()
                ->where('sprin_tipe', 'Kecamatan')
                ->where('tahap', '1')
                ->where('struktur_perangkat_daerah_id', $item['id'])
                ->first();

            if (! $sprin) {
                $sprin = Sprin::query()->create([
                    'user_id' => $userId,
                    'no_sprin' => "SPRIN-ANEV/KEC/{$item['alias']}/I/2026",
                    'no_surat_pemberitahuan' => "SP-ANEV/KEC/{$item['alias']}/I/2026",
                    'tgl_mulai' => '2026-01-10',
                    'tgl_akhir' => '2026-01-29',
                    'tim_id' => $item['tim_id'],
                    'katim' => 48,
                    'dalnis' => 40,
                    'daltu' => 33,
                    'tahap' => '1',
                    'struktur_perangkat_daerah_id' => $item['id'],
                    'sprin_tipe' => 'Kecamatan',
                    'keterangan' => 'Pengawasan dan Evaluasi Tabulasi Anev Tahap I Tahun 2026',
                    'approved_by_daltu_date' => '2026-01-08 10:00:00',
                    'created_by' => $userId,
                ]);
            }
            $sprinMap["Kecamatan_{$item['id']}"] = $sprin;
        }

        // 3. Setup PKAs per Bidang untuk masing-masing Sprin
        $bidangList = [
            'Operasional' => ['jenis_audit' => 'Audit Kinerja'],
            'Sumber Daya Manusia' => ['jenis_audit' => 'Audit Kinerja'],
            'Logistik' => ['jenis_audit' => 'Audit Kinerja'],
            'Manajemen Anggaran dan Keuangan' => ['jenis_audit' => 'Audit PNBP'],
        ];

        $pkaMap = [];
        $pkaCounter = 100;
        foreach ($sprinMap as $sprinKey => $sprin) {
            $satker = StrukturPerangkatDaerah::find($sprin->struktur_perangkat_daerah_id);
            $satkerName = $satker ? $satker->nama_unit : 'Satker';

            foreach ($bidangList as $bidangName => $meta) {
                $pka = Pka::query()
                    ->where('sprin_id', $sprin->id)
                    ->where('bidang', $bidangName)
                    ->first();

                if (! $pka) {
                    $pkaCounter++;
                    $pka = Pka::query()->create([
                        'sprin_id' => $sprin->id,
                        'nomor_pka' => "PKA/ANEV/{$sprin->id}/{$pkaCounter}/2026",
                        'tanggal' => '2026-01-12',
                        'anggaran' => 150000000,
                        'bidang' => $bidangName,
                        'jenis_audit' => $meta['jenis_audit'],
                        'sasaran_auditi' => "Evaluasi Bidang {$bidangName} pada {$satkerName}",
                        'status' => 'Final',
                        'approved_by_dalnis_date' => '2026-01-13 09:00:00',
                        'approved_by_daltu_date' => '2026-01-14 11:00:00',
                        'dibuat_oleh_id' => $userId,
                        'direviu_oleh_id' => $userId,
                        'created_by' => $userId,
                    ]);
                }
                $pkaMap["{$sprinKey}_{$bidangName}"] = $pka;
            }
        }

        // 4. Data Dummy Temuan & Uraian
        $uraianByBidang = [
            'Operasional' => [
                'temuan' => [
                    '<p>(01.01) - Ketidaksesuaian pelaksanaan kegiatan dengan SOP dan petunjuk teknis operasional</p>',
                    '<p>(01.02) - Target output kegiatan pelayanan publik tidak tercapai sesuai target indikator DPA</p>',
                    '<p>(01.03) - Keterlambatan penyelesaian pekerjaan fisik dan operasional lapangan melampaui masa kontrak</p>',
                    '<p>(01.04) - Dokumen laporan pertanggungjawaban kegiatan belum diverifikasi secara berjenjang oleh atasan langsung</p>',
                ],
                'sebab' => '<p>Kurangnya monitoring dan pengendalian rutin oleh penanggung jawab teknis kegiatan.</p>',
                'akibat' => '<p>Risiko keterlambatan pencapaian target kinerja program serta ketidaktertiban administrasi pelaporan.</p>',
                'rekomendasi' => '<p>Menginstruksikan pejabat terkait untuk meningkatkan intensitas pengawasan dan memedomani SOP.</p>',
            ],
            'Sumber Daya Manusia' => [
                'temuan' => [
                    '<p>(02.01) - Penempatan personil belum sesuai dengan analisa jabatan dan analisis beban kerja (ABK)</p>',
                    '<p>(02.02) - Pembagian tugas dan fungsi teknis belum didukung surat keputusan/surat perintah tugas resmi</p>',
                    '<p>(02.03) - Belum optimalnya pelaksanaan evaluasi kinerja berkala pegawai dan penilaian SKP</p>',
                    '<p>(02.04) - Kelemahan pencatatan kehadiran serta penegakan disiplin jam kerja aparatur</p>',
                ],
                'sebab' => '<p>Belum tertibnya pemutakhiran data kepegawaian dan pemetaan kompetensi aparatur.</p>',
                'akibat' => '<p>Beban kerja pegawai tidak merata dan efektivitas pelaksanaan tugas pokok fungsi terhambat.</p>',
                'rekomendasi' => '<p>Menyusun peta jabatan dan mendistribusikan personil sesuai keahlian serta menegakkan disiplin pegawai.</p>',
            ],
            'Logistik' => [
                'temuan' => [
                    '<p>(03.01) - Penatausahaan Barang Milik Daerah (BMD) belum tertib pada Kartu Inventaris Barang (KIB)</p>',
                    '<p>(03.02) - Peralatan kantor dan kendaraan dinas operasional belum dilakukan inventarisasi serta cek fisik berkala</p>',
                    '<p>(03.03) - Penyimpanan dan pengamanan logistik kantor belum memenuhi standar keamanan gudang penyimpanan</p>',
                    '<p>(03.04) - Keterlambatan rekonsiliasi data aset tetap dengan pengurus barang dan bidang aset</p>',
                ],
                'sebab' => '<p>Pengurus barang belum melakukan pemutakhiran berkala atas mutasi barang masuk dan keluar.</p>',
                'akibat' => '<p>Potensi kehilangan, kerusakan barang tidak terdeteksi, serta ketidakakuratan nilai aset pada neraca.</p>',
                'rekomendasi' => '<p>Melakukan sensus barang berkala, memasang kodefikasi aset, dan mempercepat rekonsiliasi data aset.</p>',
            ],
            'Manajemen Anggaran dan Keuangan' => [
                'temuan' => [
                    '<p>(04.01) - Kelemahan verifikasi bukti kelengkapan Surat Pertanggungjawaban (SPJ) belanja operasional</p>',
                    '<p>(04.02) - Keterlambatan penyetoran sisa Uang Persediaan (UP) ke Kas Daerah pada akhir tahun anggaran</p>',
                    '<p>(04.03) - Kelebihan pembayaran atas belanja perjalanan dinas dan belanja modal barang/jasa</p>',
                    '<p>(04.04) - Pajak pusat dan daerah atas belanja barang/jasa belum disetorkan tepat waktu</p>',
                ],
                'sebab' => '<p>Pejabat penatausahaan keuangan kurang teliti dalam menguji kelengkapan dokumen pertanggungjawaban.</p>',
                'akibat' => '<p>Potensi kelebihan pembayaran dan keterlambatan penerimaan kas daerah/negara.</p>',
                'rekomendasi' => '<p>Menyetorkan kembali kelebihan belanja ke Kas Daerah dan meningkatkan ketelitian verifikasi SPJ.</p>',
            ],
        ];

        // 5. Rencana Distribusi Dinas: Tepat 30 Temuan
        // Bidang: Operasional (8), SDM (7), Logistik (8), Manajemen Anggaran dan Keuangan (7)
        // Satker: DINKES (6), DISDIKBUD (7), DBMBK (6), DISHUB (5), BPKAD (6)
        $dinasPlan = [
            // DINKES: 6 temuan
            ['satker_id' => 51, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 51, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 51, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 51, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 51, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 51, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],

            // DISDIKBUD: 7 temuan
            ['satker_id' => 37, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 37, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 37, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 37, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 37, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 37, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 37, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // DBMBK: 6 temuan
            ['satker_id' => 60, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 60, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 60, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 60, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 60, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 60, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],

            // DISHUB: 5 temuan
            ['satker_id' => 165, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 165, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 165, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 165, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 165, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // BPKAD: 6 temuan
            ['satker_id' => 257, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 257, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 257, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 257, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 257, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],
            ['satker_id' => 257, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
        ];

        // 6. Rencana Distribusi Kecamatan: Tepat 58 Temuan
        // Bidang: Operasional (15), SDM (14), Logistik (15), Manajemen Anggaran dan Keuangan (14)
        // Satker: KEC-GS (10), KEC-TB (11), KEC-SA (10), KEC-BRN (9), KEC-BK (9), KEC-KL (9)
        $kecamatanPlan = [
            // Gunung Sugih: 10 temuan (3 Ops, 2 SDM, 3 Log, 2 Garkeu)
            ['satker_id' => 320, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 320, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 320, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 320, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 320, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 320, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 320, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 320, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 320, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],
            ['satker_id' => 320, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // Terbanggi Besar: 11 temuan (3 Ops, 3 SDM, 3 Log, 2 Garkeu)
            ['satker_id' => 329, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 329, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 329, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 329, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 329, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 329, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 329, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 329, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 329, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 329, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 329, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // Seputih Agung: 10 temuan (2 Ops, 3 SDM, 2 Log, 3 Garkeu)
            ['satker_id' => 338, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 338, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 338, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 338, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 338, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 338, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 338, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 338, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 338, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],
            ['satker_id' => 338, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // Bumi Ratu Nuban: 9 temuan (2 Ops, 2 SDM, 3 Log, 2 Garkeu)
            ['satker_id' => 347, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 347, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 347, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 347, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 347, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 347, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 347, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 347, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],
            ['satker_id' => 347, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // Bekri: 9 temuan (3 Ops, 2 SDM, 2 Log, 2 Garkeu)
            ['satker_id' => 356, 'bidang' => 'Operasional', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 356, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 356, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 356, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 356, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 356, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 356, 'bidang' => 'Logistik', 'status' => 'Koreksi'],
            ['satker_id' => 356, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 356, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],

            // Kalirejo: 9 temuan (2 Ops, 2 SDM, 2 Log, 3 Garkeu)
            ['satker_id' => 374, 'bidang' => 'Operasional', 'status' => 'Koreksi'],
            ['satker_id' => 374, 'bidang' => 'Operasional', 'status' => 'Selesai'],
            ['satker_id' => 374, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Koreksi'],
            ['satker_id' => 374, 'bidang' => 'Sumber Daya Manusia', 'status' => 'Selesai'],
            ['satker_id' => 374, 'bidang' => 'Logistik', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 374, 'bidang' => 'Logistik', 'status' => 'Selesai'],
            ['satker_id' => 374, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Belum Ditinjut'],
            ['satker_id' => 374, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Koreksi'],
            ['satker_id' => 374, 'bidang' => 'Manajemen Anggaran dan Keuangan', 'status' => 'Selesai'],
        ];

        // 7. Eksekusi Pembuatan Data Temuan Dinas
        $this->seedGroup('Dinas', $dinasPlan, $sprinMap, $pkaMap, $uraianByBidang, $userId);

        // 8. Eksekusi Pembuatan Data Temuan Kecamatan
        $this->seedGroup('Kecamatan', $kecamatanPlan, $sprinMap, $pkaMap, $uraianByBidang, $userId);

        $this->command?->info('Berhasil membuat 30 temuan Dinas dan 58 temuan Kecamatan secara merata!');
    }

    /**
     * Helper untuk membuat KKA dan KkaTabulasi sesuai rencana.
     */
    protected function seedGroup(
        string $lingkup,
        array $plan,
        array $sprinMap,
        array $pkaMap,
        array $uraianByBidang,
        int $userId
    ): void {
        $satkerSubUnits = [];
        $kkaCounter = 1;

        $templateKkaId = TemplateKka::query()->value('id');

        foreach ($plan as $idx => $item) {
            $satkerId = $item['satker_id'];
            $bidang = $item['bidang'];
            $status = $item['status'];

            if (! isset($satkerSubUnits[$satkerId])) {
                $satkerSubUnits[$satkerId] = StrukturPerangkatDaerah::query()
                    ->where('parent_id', $satkerId)
                    ->pluck('id')
                    ->all();
            }

            // Tentukan sub-unit untuk KKA (rotasi di antara sub-unit yang ada, atau satkerId jika tidak ada sub)
            $subs = $satkerSubUnits[$satkerId];
            $targetSubId = ! empty($subs) ? $subs[$idx % count($subs)] : $satkerId;

            $sprinKey = "{$lingkup}_{$satkerId}";
            $pkaKey = "{$sprinKey}_{$bidang}";
            $pka = $pkaMap[$pkaKey] ?? null;

            if (! $pka) {
                continue;
            }

            // Buat KKA untuk temuan ini
            $kkaNo = sprintf('KKA/ANEV/%s/%03d/2026', strtoupper(substr($lingkup, 0, 3)), $kkaCounter++);
            $kkaDate = Carbon::parse('2026-01-15')->addDays($idx % 10)->format('Y-m-d');

            $kka = Kka::query()->create([
                'nomor_kka' => $kkaNo,
                'pka_id' => $pka->id,
                'dibuat_oleh_id' => $userId,
                'direviu_oleh_id' => $userId,
                'tanggal_dibuat' => $kkaDate,
                'tanggal_direviu' => $kkaDate,
                'tanggal_kka' => $kkaDate,
                'tempat' => 'Lampung Tengah',
                'auditee_level' => $lingkup,
                'struktur_perangkat_daerah_id' => $targetSubId,
                'auditee_kode_wilayah' => (string) $targetSubId,
                'template_kka_id' => $templateKkaId,
                'tujuan' => '<p>Evaluasi dan pengujian atas ketaatan dan efektivitas pengendalian intern.</p>',
                'langkah' => '<p>Lakukan pemeriksaan substantif bukti dokumen dan uji petik fisik.</p>',
                'hasil' => '<p>Ditemukan indikasi kelemahan dalam kepatuhan administrasi dan operasional.</p>',
                'status' => 'Final',
                'approved_by_katim_date' => $kkaDate.' 10:00:00',
                'approved_by_dalnis_date' => $kkaDate.' 13:00:00',
                'approved_by_daltu_date' => $kkaDate.' 15:00:00',
                'created_by' => $userId,
            ]);

            // Siapkan uraian temuan
            $meta = $uraianByBidang[$bidang] ?? [
                'temuan' => ['<p>Kelemahan sistem pengendalian intern dan administrasi</p>'],
                'sebab' => '<p>Kurangnya pengawasan internal</p>',
                'akibat' => '<p>Risiko ketidaktertiban administrasi</p>',
                'rekomendasi' => '<p>Tingkatkan pengawasan dan lakukan pembenahan administrasi</p>',
            ];

            $uraianTemuan = $meta['temuan'][$idx % count($meta['temuan'])];
            $tindakLanjut = null;
            $statusTinjut = $status;

            if ($status === 'Selesai') {
                $tindakLanjut = '<p>Telah ditindaklanjuti secara tuntas sesuai dengan rekomendasi auditor.</p>';
            } elseif ($status === 'Koreksi') {
                $tindakLanjut = '<p>Telah ditindaklanjuti sebagian, namun dokumen bukti dukung masih memerlukan koreksi dan kelengkapan.</p>';
            }

            KkaTabulasi::query()->create([
                'kka_id' => $kka->id,
                'jenis_tabulasi' => 'Temuan',
                'bidang' => $bidang,
                'nomor' => sprintf('%02d', ($idx % 50) + 1),
                'tipe_temuan' => 'Baru',
                'kode_temuan_id' => 1,
                'sub_kode_temuan_id' => 1,
                'nilai_temuan' => null,
                'uraian_temuan' => $uraianTemuan,
                'kriteria_id' => 1,
                'uraian_kriteria' => '<p>Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah</p>',
                'kode_sebab_id' => 1,
                'sub_kode_sebab_id' => 1,
                'uraian_sebab' => $meta['sebab'],
                'kode_akibat_id' => 1,
                'sub_kode_akibat_id' => 1,
                'uraian_akibat' => $meta['akibat'],
                'kode_rekomendasi_id' => 1,
                'sub_kode_rekomendasi_id' => 1,
                'uraian_rekomendasi' => $meta['rekomendasi'],
                'auditor_id' => $userId,
                'status' => 'Final',
                'status_tinjut' => $statusTinjut,
                'kode_tinjut_id' => 1,
                'sub_kode_tinjut_id' => 1,
                'tindak_lanjut' => $tindakLanjut,
                'nilai_pengembalian' => null,
                'kesimpulan_tinjut' => $status === 'Selesai' ? '<p>Selesai sesuai rekomendasi</p>' : ($status === 'Koreksi' ? '<p>Perlu koreksi bukti</p>' : null),
                'alasan_tinjut' => null,
                'created_by' => $userId,
            ]);
        }
    }
}
