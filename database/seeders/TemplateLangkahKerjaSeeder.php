<?php

namespace Database\Seeders;

use App\Models\TemplateLangkahKerja;
use App\Models\TemplateLangkahKerjaDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateLangkahKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dinasCreator = User::query()->where('role', 'Admin Dinas')->value('id')
            ?? User::query()->where('role', 'Super Admin')->value('id')
            ?? User::query()->value('id');

        $kecamatanCreator = User::query()->where('role', 'Admin Kecamatan')->value('id')
            ?? User::query()->where('role', 'Super Admin')->value('id')
            ?? User::query()->value('id');

        $templates = [
            // --- TEMPLATE LANGKAH KERJA LINGKUP DINAS ---
            [
                'nama_template' => 'Template LK Evaluasi Kinerja Operasional dan Layanan Publik Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'details' => [
                    '<p>Dapatkan dokumen DPA, Rencana Kerja (Renja), Perjanjian Kinerja (PK), dan Laporan Kinerja Instansi Pemerintah (LKjIP) Dinas Tahun Anggaran berjalan.</p>',
                    '<p>Lakukan analisis komparatif antara target kinerja program/kegiatan dengan realisasi fisik dan penyerapan anggaran per triwulan.</p>',
                    '<p>Lakukan pengujian substantif atas bukti pertanggungjawaban kegiatan operasional lapangan serta konfirmasi kepada Pejabat Pelaksana Teknis Kegiatan (PPTK).</p>',
                    '<p>Lakukan evaluasi ketersediaan dan kepatuhan pelaksanaan Standar Operasional Prosedur (SOP) pada unit pelayanan teknis dinas.</p>',
                    '<p>Rumuskan simpulan sementara hasil evaluasi kinerja serta susun rekomendasi strategis perbaikan tata kelola bagi pimpinan dinas.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Pengujian Kepatuhan PBJ dan Pekerjaan Konstruksi Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'details' => [
                    '<p>Periksa kelengkapan berkas administrasi pengadaan barang/jasa (RUP, HPS, Dokumen Pemilihan, Surat Perjanjian Kontrak, SPMK, dan BAST).</p>',
                    '<p>Lakukan pemeriksaan fisik lapangan / uji petik bersama PPTK, konsultan pengawas, dan penyedia guna memverifikasi volume dan mutu material.</p>',
                    '<p>Uji kesesuaian pembayaran termin kontrak, pemotongan uang muka, retensi, dan pengenaan denda keterlambatan penyelesaian pekerjaan.</p>',
                    '<p>Pastikan jaminan pelaksanaan dan jaminan pemeliharaan masih aktif dan disimpan dengan aman oleh Pejabat Pembuat Komitmen (PPK).</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Pemeriksaan Kas dan Penatausahaan Keuangan Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'details' => [
                    '<p>Lakukan pemeriksaan kas mendadak (cash opname) pada brankas Bendahara Pengeluaran dan bandingkan dengan Buku Kas Umum (BKU).</p>',
                    '<p>Lakukan rekonsiliasi antara rekening koran giro dinas dengan saldo buku pembantu bank per akhir periode pemeriksaan.</p>',
                    '<p>Uji keabsahan dan kelengkapan bukti pengeluaran SPJ belanja barang/jasa, belanja modal, dan biaya perjalanan dinas.</p>',
                    '<p>Periksa kepatuhan pemungutan dan penyetoran pajak PPh dan PPN ke kas negara melalui validasi NTPN/e-Billing.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Tata Kelola Aset dan Inventarisasi BMD Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'details' => [
                    '<p>Dapatkan Daftar Barang Pengguna (DBP) dan Kartu Inventaris Barang (KIB A s.d. E) dari Pengurus Barang Pengguna dinas.</p>',
                    '<p>Lakukan cek fisik kendaraan dinas roda 2 dan roda 4, kelengkapan STNK/BPKB, serta Surat Izin Pemegang Kendaraan Dinas.</p>',
                    '<p>Lakukan inventarisasi fisik peralatan kantor, komputer, dan genset pada setiap bidang serta pastikan stiker kode barang terpasang.</p>',
                    '<p>Uji keabsahan dokumen kepemilikan dan pengamanan hukum atas tanah serta gedung kantor yang dikuasai dinas.</p>',
                ],
            ],

            // --- TEMPLATE LANGKAH KERJA LINGKUP KECAMATAN ---
            [
                'nama_template' => 'Template LK Pelayanan Administrasi Terpadu Kecamatan (PATEN)',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'details' => [
                    '<p>Periksa ketersediaan standar operasional prosedur (SOP) dan standar pelayanan minimal (SPM) untuk seluruh jenis layanan perizinan dan non-perizinan di loket PATEN.</p>',
                    '<p>Lakukan uji petik berkas permohonan layanan administrasi kependudukan dan perizinan usaha mikro untuk menguji ketepatan waktu penerbitan dokumen sesuai SOP.</p>',
                    '<p>Periksa ketersediaan buku register pelayanan, buku tamu/antrean, sarana pengaduan masyarakat, dan kotak saran di loket front office.</p>',
                    '<p>Lakukan wawancara dan survei kepuasan masyarakat terhadap pemohon layanan mengenai kepastian biaya (bebas pungli) dan keramahan petugas pelayanan.</p>',
                    '<p>Uji kepatuhan pelaporan rekapitulasi data pelayanan bulanan yang disampaikan Camat kepada Bupati melalui Bagian Tata Pemerintahan.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Pengawasan dan Fasilitasi Keuangan Desa/Kampung',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'details' => [
                    '<p>Dapatkan berkas laporan evaluasi rancangan APB-Kampung dan laporan pertanggungjawaban realisasi oleh Tim Fasilitasi Tingkat Kecamatan.</p>',
                    '<p>Uji kepatuhan jadwal pelaksanaan verifikasi SPJ Dana Desa (DD) dan Alokasi Dana Kampung (ADK) oleh seksi PMD sebelum rekomendasi pencairan diterbitkan.</p>',
                    '<p>Lakukan uji petik laporan hasil monitoring dan evaluasi (Monev) lapangan atas pembangunan fisik infrastruktur kampung yang didampingi tim kecamatan.</p>',
                    '<p>Konfirmasi tindak lanjut pemerintah kampung atas catatan/koreksi administrasi dan rekomendasi pembinaan yang diberikan oleh pihak kecamatan.</p>',
                    '<p>Periksa arsip berita acara rekonsiliasi dan laporan pembinaan tata kelola keuangan desa semesteran ke Dinas PMD Kabupaten.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Pengujian Kas dan SPJ Belanja Operasional Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'details' => [
                    '<p>Lakukan pemeriksaan kas fisik (cash opname) mendadak pada brankas Bendahara Pengeluaran dan bandingkan dengan saldo akhir Buku Kas Umum (BKU).</p>',
                    '<p>Lakukan rekonsiliasi bank antara rekening giro operasional kecamatan dengan saldo buku pembantu bank per akhir periode audit.</p>',
                    '<p>Uji keabsahan bukti kuitansi, faktur, nota pesanan, dan berita acara serah terima atas belanja barang, jasa, ATK, dan makan minum rapat kantor kecamatan.</p>',
                    '<p>Periksa kepatuhan pemotongan dan penyetoran Pajak Penghasilan (PPh 21, PPh 22, PPh 23) serta PPN ke kas negara melalui validasi NTPN/e-Billing.</p>',
                    '<p>Evaluasi ketertiban pembukuan buku pembantu panjar serta penandatanganan Berita Acara Penutupan Kas bulanan oleh Camat selaku Pengguna Anggaran.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Penatausahaan dan Pengamanan BMD Kantor Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'details' => [
                    '<p>Dapatkan Daftar Barang Pengguna (DBP) dan Kartu Inventaris Barang (KIB A, B, C, D, E) Kantor Kecamatan per tahun anggaran berjalan.</p>',
                    '<p>Lakukan cek fisik kendaraan dinas operasional (roda 2 dan roda 4), periksa kelengkapan STNK, BPKB, dan kesesuaian pemegang kendaraan dinas.</p>',
                    '<p>Uji kewajaran realisasi belanja BBM, perawatan rutin, dan perbaikan kendaraan dinas terhadap catatan jarak tempuh / log book perjalanan dinas.</p>',
                    '<p>Lakukan inventarisasi fisik peralatan kantor (komputer, laptop, printer, AC, genset) pada masing-masing seksi dan pastikan stiker kodefikasi aset terpasang.</p>',
                    '<p>Periksa kejelasan status hukum dan arsip sertifikat kepemilikan tanah kantor kecamatan serta rumah dinas jabatan camat.</p>',
                ],
            ],
            [
                'nama_template' => 'Template LK Disiplin Aparatur dan Penyelenggaraan Trantibum Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'details' => [
                    '<p>Uji rekapitulasi daftar hadir elektronik / fingerprint seluruh pegawai ASN dan Tenaga Harian Lepas (THL) kantor kecamatan per bulan penugasan.</p>',
                    '<p>Evaluasi kesesuaian uraian tugas jabatan (job description) staf pada masing-masing seksi dengan Surat Keputusan (SK) pembagian tugas yang ditetapkan Camat.</p>',
                    '<p>Periksa dokumen jadwal piket, laporan patroli ketentraman dan ketertiban umum (Trantibum), serta koordinasi forkopimcam dengan Polsek dan Koramil.</p>',
                    '<p>Evaluasi penanganan aduan gangguan ketenteraman lingkungan masyarakat dan kesiapsiagaan posko kebencanaan di tingkat kecamatan.</p>',
                ],
            ],
        ];

        foreach ($templates as $data) {
            $details = $data['details'];
            unset($data['details']);

            $template = TemplateLangkahKerja::query()->updateOrCreate(
                [
                    'nama_template' => $data['nama_template'],
                    'lingkup' => $data['lingkup'],
                ],
                $data
            );

            // Bersihkan detail lama agar idempoten
            $template->details()->forceDelete();

            foreach ($details as $uraian) {
                TemplateLangkahKerjaDetail::query()->create([
                    'template_langkah_kerja_id' => $template->id,
                    'uraian' => $uraian,
                    'created_by' => $data['created_by'],
                ]);
            }

            $template->refreshJumlahLangkahKerja();
        }

        $this->command?->info('Berhasil membuat/memperbarui seeder Template Langkah Kerja (Dinas & Kecamatan).');
    }
}
