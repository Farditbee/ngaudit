<?php

namespace Database\Seeders;

use App\Models\Plotting;
use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateKkaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creatorId = User::query()->where('role', 'Super Admin')->value('id')
            ?? User::query()->value('id');

        // Pastikan Plotting WIL I - WIL IV tersedia
        $wilConfigs = [
            1 => 'WIL I',
            2 => 'WIL II',
            3 => 'WIL III',
            4 => 'WIL IV',
        ];

        $plottings = [];
        foreach ($wilConfigs as $id => $plotName) {
            $plottings[$plotName] = Plotting::query()->where('plot', $plotName)->first()
                ?? Plotting::query()->firstOrCreate(['id' => $id], ['plot' => $plotName]);
        }

        // Pastikan Provinsi Jawa Barat (32), Jawa Tengah (33), Jawa Timur (35) tersedia
        $provinceConfigs = [
            '32' => ['name' => 'JAWA BARAT', 'label' => 'Jawa Barat'],
            '33' => ['name' => 'JAWA TENGAH', 'label' => 'Jawa Tengah'],
            '35' => ['name' => 'JAWA TIMUR', 'label' => 'Jawa Timur'],
        ];

        foreach ($provinceConfigs as $code => $info) {
            $exists = DB::table('wilayah')
                ->where('kode_wilayah', (string) $code)
                ->whereNull('kabupaten_kota')
                ->exists();

            if (! $exists) {
                DB::table('wilayah')->insert([
                    'kode_wilayah' => (string) $code,
                    'provinsi' => $info['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $topics = $this->getTemplateTopics();

        // Bersihkan data lama agar jumlah template bersih (20 Nasional + 15 Wilayah = 35 Template)
        TemplateKka::withTrashed()->forceDelete();

        // 1. Seed 5 Template KKA untuk masing-masing Nasional (WIL I - WIL IV) -> 20 Template
        foreach ($plottings as $plotName => $plotting) {
            $wilAdminId = User::query()
                ->where('plotting_id', $plotting->id)
                ->value('id') ?? $creatorId;

            foreach ($topics as $topic) {
                $scopeTitle = "Lingkup Nasional ({$plotName})";
                $namaTemplate = "{$topic['title_prefix']} - {$plotName}";

                TemplateKka::query()->updateOrCreate(
                    [
                        'nama_template' => $namaTemplate,
                        'lingkup' => 'Nasional',
                        'plotting_id' => $plotting->id,
                    ],
                    [
                        'kode_wilayah' => null,
                        'tujuan' => str_replace('{SCOPE}', $scopeTitle, $topic['tujuan']),
                        'langkah' => str_replace('{SCOPE}', $scopeTitle, $topic['langkah']),
                        'hasil' => str_replace('{SCOPE}', $scopeTitle, $topic['hasil']),
                        'created_by' => $wilAdminId,
                    ]
                );
            }
        }

        // 2. Seed 5 Template KKA untuk masing-masing Wilayah (Jawa Barat, Jawa Tengah, Jawa Timur) -> 15 Template
        foreach ($provinceConfigs as $code => $info) {
            $provCode = (string) $code;
            $provLabel = $info['label'];

            $provAdminId = User::query()
                ->where('kode_wilayah', 'like', "{$provCode}%")
                ->value('id') ?? $creatorId;

            foreach ($topics as $topic) {
                $scopeTitle = "Wilayah Provinsi {$provLabel}";
                $namaTemplate = "{$topic['title_prefix']} - Provinsi {$provLabel}";

                TemplateKka::query()->updateOrCreate(
                    [
                        'nama_template' => $namaTemplate,
                        'lingkup' => 'Wilayah',
                        'kode_wilayah' => $provCode,
                    ],
                    [
                        'plotting_id' => null,
                        'tujuan' => str_replace('{SCOPE}', $scopeTitle, $topic['tujuan']),
                        'langkah' => str_replace('{SCOPE}', $scopeTitle, $topic['langkah']),
                        'hasil' => str_replace('{SCOPE}', $scopeTitle, $topic['hasil']),
                        'created_by' => $provAdminId,
                    ]
                );
            }
        }
    }

    /**
     * @return list<array{title_prefix: string, tujuan: string, langkah: string, hasil: string}>
     */
    protected function getTemplateTopics(): array
    {
        return [
            [
                'title_prefix' => 'KKA Evaluasi Kinerja Operasional dan Layanan Publik',
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Evaluasi Kinerja Operasional pada <strong>{SCOPE}</strong> ini disusun dengan tujuan untuk:</p>
<ol>
    <li>Menilai tingkat efektivitas pencapaian Indikator Kinerja Utama (IKU) dan realisasi target Perjanjian Kinerja (PK) tahun berjalan.</li>
    <li>Mengukur efisiensi pemanfaatan sumber daya operasional dalam mendukung penyelenggaraan layanan publik di satuan kerja.</li>
    <li>Mengidentifikasi hambatan teknis di lapangan serta mengevaluasi kecukupan Sistem Pengendalian Intern (SPI) pada kegiatan operasional.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan dokumen Rencana Kerja dan Anggaran (RKA), Perjanjian Kinerja (PK), SOP Operasional, serta Laporan Capaian Kinerja Triwulanan pada <strong>{SCOPE}</strong>.</li>
    <li>Lakukan analisis komparatif antara target output/outcome kegiatan dengan realisasi fisik dan penyerapan anggaran hingga periode pemeriksaan.</li>
    <li>Lakukan uji petik dan observasi langsung ke unit layanan operasional serta wawancara dengan pejabat pelaksana teknis kegiatan.</li>
    <li>Evaluasi kepatuhan pelaporan berkala dan tindak lanjut atas kendala operasional yang ditemukan pada periode sebelumnya.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Berdasarkan hasil pengujian dokumen dan observasi lapangan pada <strong>{SCOPE}</strong>, diperoleh simpulan sebagai berikut:</p>
<table>
    <thead>
        <tr>
            <th>Aspek Pengujian</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria / Acuan</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Capaian Indikator Kinerja</td>
            <td>Realisasi rata-rata capaian indikator operasional telah memenuhi target triwulanan yang ditetapkan.</td>
            <td>Perjanjian Kinerja & Pedoman Evaluasi Kinerja Tahunan.</td>
            <td>Memadai, perlu optimalisasi dokumentasi bukti dukung capaian.</td>
        </tr>
        <tr>
            <td>Kepatuhan SOP Layanan</td>
            <td>Pelaksanaan layanan telah mengacu pada SOP, namun pemutakhiran papan informasi layanan belum seragam.</td>
            <td>Standar Pelayanan Publik & Peraturan Menteri terkait.</td>
            <td>Disarankan pemutakhiran berkala atas standar operasional layanan.</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'title_prefix' => 'KKA Pengujian Kepatuhan Pengadaan Barang/Jasa dan Logistik',
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Pengadaan Barang/Jasa dan Logistik pada <strong>{SCOPE}</strong> bertujuan untuk:</p>
<ol>
    <li>Memastikan kewajaran penyusunan Harga Perkiraan Sendiri (HPS) dan kesesuaian spesifikasi teknis barang/jasa yang diadakan.</li>
    <li>Menguji ketaatan prosedur pemilihan penyedia (Tender, E-Purchasing/E-Katalog, dan Pengadaan Langsung) terhadap regulasi PBJ.</li>
    <li>Memverifikasi ketepatan waktu pelaksanaan kontrak, kebenaran Berita Acara Serah Terima (BAST), serta eksistensi fisik dan kualitas logistik.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Himpun dokumen RUP, KAK, Kertas Kerja HPS, Dokumen Pemilihan, Surat Perjanjian/Kontrak, Addendum, dan BAST pada satuan kerja di <strong>{SCOPE}</strong>.</li>
    <li>Uji kewajaran sumber data survei harga pasar pada penyusunan HPS dan pastikan tidak terdapat pemecahan paket untuk menghindari tender.</li>
    <li>Lakukan pemeriksaan fisik barang/pekerjaan di lokasi bersama Pejabat Pembuat Komitmen (PPK) dan tuangkan dalam Berita Acara Pemeriksaan Fisik.</li>
    <li>Telusuri dokumen pencairan pembayaran (kuitansi, faktur pajak, SP2D) dan hitung ketepatan pengenaan denda keterlambatan apabila melampaui masa kontrak.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Dari hasil reviu kontrak dan cek fisik lapangan atas sampel paket pengadaan pada <strong>{SCOPE}</strong>:</p>
<ul>
    <li><strong>Kondisi:</strong> Proses pemilihan penyedia dan penandatanganan kontrak telah dilaksanakan melalui sistem elektronik (SPSE / E-Katalog), serta volume fisik barang telah sesuai dengan BAST.</li>
    <li><strong>Kriteria:</strong> Perpres Nomor 16 Tahun 2018 jo. Perpres Nomor 12 Tahun 2021 tentang Pengadaan Barang/Jasa Pemerintah.</li>
    <li><strong>Sebab & Akibat:</strong> Dokumentasi kertas kerja survei harga HPS pada beberapa paket pengadaan langsung masih perlu dilengkapi referensi pembanding tertulis.</li>
    <li><strong>Simpulan:</strong> Pelaksanaan pengadaan barang/jasa secara umum telah patuh ketentuan dengan catatan penguatan tertib arsip penyusunan HPS.</li>
</ul>
HTML,
            ],
            [
                'title_prefix' => 'KKA Pemeriksaan Manajemen Anggaran dan Penatausahaan Keuangan',
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Pengujian Manajemen Anggaran dan Penatausahaan Keuangan pada <strong>{SCOPE}</strong> dilaksanakan untuk:</p>
<ol>
    <li>Meyakini keakuratan saldo kas di Bendahara Pengeluaran/Penerimaan serta kesesuaiannya dengan Buku Kas Umum (BKU) dan rekening koran.</li>
    <li>Menilai validitas, kelengkapan, dan keabsahan bukti pertanggungjawaban belanja operasional, belanja modal, serta perjalanan dinas.</li>
    <li>Memastikan kepatuhan pemungutan dan penyetoran pajak (PPN/PPh) serta ketepatan klasifikasi akun belanja (MAK).</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Lakukan kas opname dan rekonsiliasi saldo BKU, Buku Pembantu Kas Tunai, Buku Pembantu Bank, dan Rekening Koran Bendahara pada <strong>{SCOPE}</strong>.</li>
    <li>Lakukan <em>vouching</em> secara uji petik atas dokumen SPP, SPM, SP2D beserta bukti kuitansi, daftar nominatif honorarium, dan bukti perjalanan dinas (Surat Tugas, SPD, tiket, dan invoice penginapan).</li>
    <li>Periksa bukti penerimaan negara (NTPN) atas pemotongan/pemungutan pajak oleh bendahara dan bandingkan dengan Buku Pembantu Pajak.</li>
    <li>Analisis kepatuhan revisi anggaran (DIPA/POK) terhadap ketentuan tata cara revisi anggaran tahun berjalan.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Berdasarkan hasil kas opname dan pengujian substantif atas transaksi keuangan pada <strong>{SCOPE}</strong>:</p>
<ol>
    <li><strong>Kesesuaian Kas:</strong> Saldo kas tunai dan saldo bank pada BKU telah sesuai dengan fisik uang tunai serta rekening koran per tanggal pemeriksaan (selisih Rp 0,-).</li>
    <li><strong>Pertanggungjawaban Belanja:</strong> Bukti-bukti pengeluaran telah didukung dokumen yang lengkap dan sah serta diotorisasi oleh pejabat berwenang.</li>
    <li><strong>Kewajiban Perpajakan:</strong> Seluruh potongan pajak atas transaksi belanja telah disetorkan tepat waktu ke Kas Negara dan memiliki NTPN yang valid.</li>
</ol>
HTML,
            ],
            [
                'title_prefix' => 'KKA Audit Tata Kelola SDM, Disiplin dan Belanja Pegawai',
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Tata Kelola Sumber Daya Manusia (SDM) pada <strong>{SCOPE}</strong> bertujuan untuk:</p>
<ol>
    <li>Menilai kesesuaian penempatan personel, beban kerja, dan pemenuhan kompetensi jabatan pada unit kerja.</li>
    <li>Menguji ketertiban administrasi kehadiran, penegakan disiplin pegawai, serta penilaian sasaran kinerja pegawai (SKP).</li>
    <li>Memastikan ketepatan pembayaran gaji, tunjangan kinerja, uang makan, dan uang lembur sesuai dengan hak serta rekapitulasi presensi aktual.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan daftar nominatif pegawai (DUK/Bezetting), peta jabatan, rekapitulasi presensi elektronik, serta daftar pembayaran gaji dan tunjangan kinerja pada <strong>{SCOPE}</strong>.</li>
    <li>Bandingkan data kehadiran, cuti, dan penugasan luar kantor dengan perhitungan pemotongan tunjangan kinerja serta pembayaran uang makan.</li>
    <li>Uji kelengkapan dokumen Surat Perintah Kerja Lembur (SPKL), daftar hadir lembur, dan laporan hasil kerja lembur.</li>
    <li>Verifikasi pemenuhan dokumen penilaian kinerja periodik (SKP) dan tindak lanjut pembinaan disiplin pegawai.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Hasil pengujian atas tata kelola kepegawaian dan belanja pegawai pada <strong>{SCOPE}</strong> menunjukkan:</p>
<ul>
    <li><strong>Administrasi & Disiplin:</strong> Pencatatan kehadiran telah terintegrasi secara elektronik dan menjadi dasar perhitungan tunjangan kinerja secara akurat.</li>
    <li><strong>Pembayaran Hak Pegawai:</strong> Tidak ditemukan kelebihan pembayaran atas gaji, tunjangan kinerja, maupun uang makan pegawai pada periode yang diuji.</li>
    <li><strong>Pengembangan Kompetensi:</strong> Sebagian besar pegawai telah memenuhi target jam pelatihan tahunan, namun pemetaan kebutuhan diklat teknis masih perlu diperbarui.</li>
</ul>
HTML,
            ],
            [
                'title_prefix' => 'KKA Verifikasi Pengelolaan Aset / BMN dan Inventarisasi Fisik',
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Pengelolaan Aset dan Barang Milik Negara/Daerah (BMN) pada <strong>{SCOPE}</strong> disusun untuk:</p>
<ol>
    <li>Memastikan keakuratan pencatatan aset tetap dan persediaan pada aplikasi penatausahaan aset (SIMAK-BMN / SAKTI) dibandingkan dengan laporan keuangan.</li>
    <li>Memverifikasi keberadaan fisik, kondisi, pengamanan hukum/administratif, serta ketepatan penggunaan aset operasional di lapangan.</li>
    <li>Menilai kepatuhan prosedur pemanfaatan, pemeliharaan, hingga penghapusan aset yang rusak berat atau usang.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan Laporan Barang Kuasa Pengguna (LBKP), Daftar Barang Ruangan (DBR), Kartu Inventaris Barang (KIB), dan Laporan Persediaan pada <strong>{SCOPE}</strong>.</li>
    <li>Lakukan rekonsiliasi nilai perolehan dan akumulasi penyusutan aset antara laporan penatausahaan BMN dengan Neraca Laporan Keuangan.</li>
    <li>Lakukan cek fisik (inventarisasi/stock opname) secara uji petik atas kendaraan dinas, peralatan dan mesin, gedung bangunan, serta barang persediaan di gudang.</li>
    <li>Periksa kelengkapan dokumen kepemilikan (sertifikat tanah, BPKB/STNK kendaraan, PSP) serta surat izin penghapusan bagi aset yang diusulkan hapus.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Berdasarkan rekonsiliasi data dan pemeriksaan fisik aset di lingkungan <strong>{SCOPE}</strong>:</p>
<table>
    <thead>
        <tr>
            <th>Kategori Aset</th>
            <th>Hasil Pemeriksaan Fisik & Dokumen</th>
            <th>Status & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Aset Tetap (Peralatan & Kendaraan)</td>
            <td>Fisik aset ditemukan sesuai lokasi DBR/KIB, berfungsi baik, dan telah diberikan label kodefikasi BMN.</td>
            <td>Memadai; pertahankan pemutakhiran DBR setiap terjadi mutasi ruang.</td>
        </tr>
        <tr>
            <td>Barang Persediaan</td>
            <td>Saldo fisik hasil <em>stock opname</em> gudang telah sesuai dengan Kartu Barang dan Laporan Persediaan.</td>
            <td>Memadai; tata letak penyimpanan gudang telah menerapkan prinsip FIFO.</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
        ];
    }
}
