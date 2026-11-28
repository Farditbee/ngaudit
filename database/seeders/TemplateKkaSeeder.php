<?php

namespace Database\Seeders;

use App\Models\TemplateKka;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateKkaSeeder extends Seeder
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
            // --- TEMPLATE KKA LINGKUP DINAS ---
            [
                'nama_template' => 'KKA Evaluasi Kinerja Operasional dan Layanan Publik - Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Evaluasi Kinerja Operasional Perangkat Daerah Lingkup Dinas ini disusun dengan tujuan untuk:</p>
<ol>
    <li>Menilai tingkat ketercapaian target kinerja program dan kegiatan operasional pada DPA Dinas Tahun Anggaran 2025.</li>
    <li>Mengukur efisiensi alokasi dan penyerapan anggaran dalam mendukung capaian output layanan publik.</li>
    <li>Mengidentifikasi kelemahan Sistem Pengendalian Intern (SPI) serta merumuskan rekomendasi korektif bernilai tambah.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan dokumen DPA, Rencana Kerja (Renja), Perjanjian Kinerja (PK), dan Laporan Kinerja Instansi Pemerintah (LKjIP) Dinas.</li>
    <li>Lakukan analisis komparatif antara target kinerja fisik dengan realisasi penyerapan anggaran per triwulan.</li>
    <li>Lakukan uji petik bukti pertanggungjawaban kegiatan operasional lapangan serta konfirmasi kepada Pejabat Pelaksana Teknis Kegiatan (PPTK).</li>
    <li>Evaluasi ketersediaan dan implementasi SOP pada unit pelayanan teknis dinas.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Berdasarkan hasil pengujian dokumen dan wawancara lapangan, dirumuskan kondisi faktual, kriteria acuan, serta simpulan hasil pengujian kinerja dinas.</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Uraian Pengujian</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria Regulasi</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Capaian Output Kegiatan</td>
            <td>Target output tercapai 92% dari target renja</td>
            <td>Perjanjian Kinerja DPA-SKPD</td>
            <td>Tingkatkan pengendalian jadwal pelaksanaan kegiatan</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'nama_template' => 'KKA Pengujian Kepatuhan Pengadaan Barang/Jasa dan Logistik - Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Memastikan seluruh tahapan proses pengadaan barang/jasa pada Dinas telah mematuhi ketentuan perundang-undangan serta spesifikasi kontrak.</p>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Periksa kelengkapan dokumen pengadaan (RUP, HPS, Dokumen Pemilihan, Surat Perjanjian Kontrak, dan BAST).</li>
    <li>Lakukan pemeriksaan fisik ke lokasi pekerjaan bersama PPTK dan pengawas teknis guna cek volume dan spesifikasi material.</li>
    <li>Periksa perhitungan pembayaran termin, pemotongan uang muka, denda keterlambatan, dan jaminan pemeliharaan.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Hasil pengujian fisik dan administrasi kontrak pengadaan barang/jasa dinas disajikan dalam tabel rekapitulasi temuan pengadaan.</p>
HTML,
            ],
            [
                'nama_template' => 'KKA Pemeriksaan Manajemen Anggaran dan Penatausahaan Keuangan - Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Menguji ketertiban penatausahaan kas oleh bendahara pengeluaran serta keabsahan bukti pertanggungjawaban belanja dinas.</p>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Lakukan pemeriksaan fisik kas (cash opname) pada brankas bendahara dan rekonsiliasi saldo bank.</li>
    <li>Uji substantif bukti kuitansi SPJ belanja barang/jasa, belanja modal, dan perjalanan dinas.</li>
    <li>Periksa ketaatan pemotongan dan penyetoran pajak ke kas negara.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Hasil rekonsiliasi kas, uji petik bukti SPJ, dan konfirmasi perpajakan disajikan dalam matriks temuan pemeriksaan.</p>
HTML,
            ],
            [
                'nama_template' => 'KKA Audit Tata Kelola SDM, Disiplin dan Belanja Pegawai - Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Menilai efektivitas manajemen kepegawaian, tingkat kehadiran/disiplin ASN, dan akuntabilitas pembayaran belanja pegawai/tunjangan.</p>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan daftar nominatif pegawai, rekapitulasi presensi absensi elektronik, dan dokumen TPP/gaji.</li>
    <li>Uji kesesuaian pembayaran Tambahan Penghasilan Pegawai (TPP) dengan capaian kinerja dan potongan absensi.</li>
    <li>Lakukan evaluasi atas penegakan disiplin ASN dan tindak lanjut atas pelanggaran disiplin kerja.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Ringkasan hasil evaluasi presensi dan akuntabilitas pembayaran belanja pegawai dinas.</p>
HTML,
            ],
            [
                'nama_template' => 'KKA Verifikasi Pengelolaan Aset / BMN dan Inventarisasi Fisik - Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Menguji keberadaan fisik, pencatatan SIMDA-BMD, status pengamanan hukum, dan pemanfaatan aset tetap pada dinas.</p>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan Buku Inventaris, KIB A s.d. E, dan Daftar Mutasi Barang dinas.</li>
    <li>Lakukan cek fisik kendaraan dinas dan peralatan kantor serta verifikasi stiker inventaris.</li>
    <li>Periksa kelengkapan bukti kepemilikan (BPKB/STNK/Sertifikat tanah) dan pemanfaatan aset pihak ketiga.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Simpulan hasil cek fisik aset dan rekomendasi tertib administrasi Barang Milik Daerah.</p>
HTML,
            ],

            // --- TEMPLATE KKA LINGKUP KECAMATAN ---
            [
                'nama_template' => 'KKA Evaluasi Kinerja Pelayanan Administrasi Terpadu Kecamatan (PATEN) - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) ini disusun dengan tujuan untuk:</p>
<ol>
    <li>Menilai efektivitas dan ketepatan waktu penerbitan dokumen pelayanan izin dan non-izin pada loket PATEN Kecamatan.</li>
    <li>Menguji transparansi persyaratan pelayanan, ketiadaan pungutan liar (pungli), dan kepatuhan terhadap Standar Operasional Prosedur (SOP).</li>
    <li>Menilai kelayakan sarana prasarana front office, loket informasi, dan penanganan pengaduan masyarakat.</li>
    <li>Memberikan rekomendasi perbaikan mutu pelayanan publik kecamatan yang bernilai tambah bagi kepuasan warga.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan dokumen SK Tim Penyelenggara PATEN, SOP Pelayanan, Maklumat Pelayanan, dan Standar Biaya Pelayanan (bila ada retribusi resmi).</li>
    <li>Lakukan uji petik minimal 15 (lima belas) berkas permohonan layanan administrasi kependudukan (KK, KTP, Surat Pindah) dan rekomendasi perizinan usaha mikro.</li>
    <li>Bandingkan tanggal masuk berkas pada buku register dengan tanggal penandatanganan dan penyerahan dokumen kepada pemohon (uji durasi janji layanan).</li>
    <li>Lakukan observasi langsung ke loket PATEN dan wawancara singkat dengan 3-5 pemohon layanan di ruang tunggu kecamatan.</li>
    <li>Periksa ketersediaan dan fungsi kotak pengaduan, buku register aduan, dan mekanisme tindak lanjut atas keluhan warga.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Berdasarkan hasil pengujian sampel berkas layanan dan observasi loket PATEN Kecamatan, dirumuskan hasil pemeriksaan sebagai berikut:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Indikator Pelayanan</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria / Acuan</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Ketepatan Waktu Layanan</td>
            <td>Rata-rata penerbitan rekomendasi izin 1-2 hari kerja sesuai SOP</td>
            <td>SOP PATEN & Maklumat Pelayanan</td>
            <td>Memenuhi standar waktu layanan</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Transparansi Persyaratan & Biaya</td>
            <td>Bagan alur pelayanan terpasang jelas di loket dan tidak ditemukan pungutan liar</td>
            <td>Permendagri No. 4/2010 & UU No. 25/2009</td>
            <td>Pertahankan integritas pelayanan prima</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Pengelolaan Pengaduan Warga</td>
            <td>Kotak saran tersedia, namun buku register rekap aduan belum diisi secara tertib</td>
            <td>Standar Pelayanan Publik</td>
            <td>Tugaskan petugas khusus untuk mencatat dan merekap aduan warga bulanan</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'nama_template' => 'KKA Pengujian Pembinaan dan Pengawasan Pengelolaan Keuangan Desa - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) ini disusun untuk:</p>
<ol>
    <li>Menguji kepatuhan dan efektivitas tim fasilitasi kecamatan dalam mengevaluasi rancangan APB-Kampung dan perubahan APB-Kampung.</li>
    <li>Memastikan keabsahan verifikasi dokumen administrasi SPJ Dana Desa (DD) dan Alokasi Dana Kampung (ADK) oleh seksi PMD kecamatan.</li>
    <li>Menilai keteraturan pelaksanaan monitoring dan evaluasi (monev) lapangan atas pembangunan fisik infrastruktur kampung.</li>
    <li>Mendorong pembinaan tertib pembukuan keuangan desa guna mencegah potensi penyimpangan dana di tingkat kampung.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan SK Tim Fasilitasi dan Evaluasi APB-Kampung Tingkat Kecamatan Tahun Anggaran 2025.</li>
    <li>Periksa checklist verifikasi berkas permohonan rekomendasi pencairan DD/ADK untuk setiap kampung di wilayah kerja kecamatan.</li>
    <li>Uji apakah rekomendasi pencairan dana tahap berikutnya hanya diterbitkan setelah laporan realisasi tahap sebelumnya diverifikasi lengkap.</li>
    <li>Periksa dokumen laporan hasil monev lapangan pembangunan fisik kampung yang ditandatangani Camat dan Kasi PMD.</li>
    <li>Konfirmasi tindak lanjut kepala kampung terhadap catatan/rekomendasi pembinaan yang diberikan oleh tim kecamatan.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Hasil pengujian atas fungsi pembinaan dan pengawasan pengelolaan keuangan desa oleh pihak kecamatan disajikan dalam tabel simpulan berikut:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Fokus Pembinaan & Monev</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria Regulasi</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Evaluasi Raperdes APB-Kampung</td>
            <td>Seluruh kampung telah dilakukan evaluasi rancangan APB-Kampung tepat waktu</td>
            <td>Permendagri No. 20/2018 & Permendagri No. 73/2020</td>
            <td>Proses fasilitasi berjalan efektif</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Verifikasi Berkas SPJ Pencairan</td>
            <td>Terdapat kelambatan penyerahan SPJ tahap II pada 2 kampung binaan</td>
            <td>Perbup Pengelolaan Alokasi Dana Kampung</td>
            <td>Tingkatkan pendampingan dan teguran tertulis kepada kampung yang terlambat SPJ</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Laporan Monev Fisik Lapangan</td>
            <td>Dokumentasi foto fisik monev dan berita acara telah dibuat untuk seluruh proyek</td>
            <td>Juknis Pengawasan Camat atas Desa</td>
            <td>Pertahankan keteraturan dokumentasi monev fisik</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'nama_template' => 'KKA Pemeriksaan Kas, Belanja Operasional dan Pajak Bendahara - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Pemeriksaan Kas dan Belanja Kantor Kecamatan ini bertujuan untuk:</p>
<ol>
    <li>Menguji keabsahan fisik uang tunai pada brankas Bendahara Pengeluaran Kecamatan (cash opname).</li>
    <li>Menguji ketertiban pembukuan Buku Kas Umum (BKU), buku pembantu kas, dan rekonsiliasi rekening giro bank kecamatan.</li>
    <li>Memverifikasi keabsahan bukti pendukung (SPJ) belanja barang, jasa, ATK, dan honorarium kegiatan operasional kantor camat.</li>
    <li>Memastikan ketaatan bendahara dalam pemotongan dan penyetoran pajak PPh dan PPN ke kas negara tepat waktu.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Lakukan perhitungan fisik kas (cash opname) mendadak bersama Bendahara Pengeluaran disaksikan Camat selaku PA.</li>
    <li>Bandingkan saldo fisik kas tunai dan rekening koran bank dengan saldo akhir pada Buku Kas Umum (BKU) per tanggal audit.</li>
    <li>Uji petik sampel bukti belanja (kuitansi, faktur/nota toko, nota pesanan, dan daftar penerima honorarium) minimal 20 transaksi.</li>
    <li>Periksa keabsahan stempel toko, tanda tangan penerima, dan kesesuaian harga satuan dengan Standar Biaya Masukan (SBM) Kabupaten.</li>
    <li>Periksa bukti penerimaan negara (BPN) dan Kode Billing atas setoran PPh Pasal 21, 22, 23, serta PPN belanja operasional.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Simpulan hasil cash opname, verifikasi bukti SPJ, dan pengujian perpajakan Bendahara Pengeluaran Kecamatan:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Aspek Pemeriksaan</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria Regulasi</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Pemeriksaan Fisik Kas (Cash Opname)</td>
            <td>Saldo fisik uang tunai dan rekening bank cocok dengan saldo penutupan BKU (nihil selisih)</td>
            <td>PP No. 12/2019 & Permendagri No. 77/2020</td>
            <td>Pengelolaan kas tertib dan akuntabel</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Kelengkapan Bukti SPJ Belanja</td>
            <td>Bukti kuitansi dan nota belanja telah lengkap didukung tanda terima barang</td>
            <td>Standar Akuntansi Pemerintahan</td>
            <td>Pertahankan ketertiban administrasi bukti belanja</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Penyetoran Pajak Belanja</td>
            <td>Kewajiban pemotongan pajak telah disetorkan seluruhnya sebelum tanggal 10 bulan berikutnya</td>
            <td>Undang-Undang Ketentuan Umum Perpajakan</td>
            <td>Ketaatan perpajakan berjalan sangat baik</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'nama_template' => 'KKA Inventarisasi dan Pemanfaatan Barang Milik Daerah (BMD) - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) Inventarisasi Aset BMD Kecamatan disusun guna:</p>
<ol>
    <li>Menguji kebenaran fisik dan pencatatan seluruh aset tetap kantor kecamatan pada Kartu Inventaris Barang (KIB A s.d. E).</li>
    <li>Memeriksa keberadaan fisik kendaraan dinas operasional (roda 2 dan roda 4), kelengkapan STNK/BPKB, serta status pemegangnya.</li>
    <li>Mengevaluasi kewajaran pengeluaran belanja bahan bakar minyak (BBM) dan biaya servis pemeliharaan rutin kendaraan dinas.</li>
    <li>Memastikan pengamanan hukum atas tanah dan bangunan kantor kecamatan serta rumah dinas camat.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan Daftar Barang Pengguna (DBP) dan KIB A, B, C, E Kantor Kecamatan dari Pengurus Barang Pengguna.</li>
    <li>Lakukan cek fisik seluruh kendaraan dinas bermotor, periksa nomor rangka, nomor mesin, dan masa berlaku pajak STNK.</li>
    <li>Periksa Surat Izin Pemegang Kendaraan Dinas dan pastikan digunakan oleh pejabat/pegawai yang sah sesuai tupoksi.</li>
    <li>Lakukan uji petik peralatan kantor (laptop, komputer PC, printer, AC, genset) di setiap seksi dan pastikan terpasang kodefikasi barang.</li>
    <li>Periksa arsip sertifikat kepemilikan tanah kantor kecamatan dan pastikan tercatat atas nama Pemerintah Kabupaten Lampung Tengah.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Ringkasan hasil inventarisasi fisik aset tetap dan penatausahaan Barang Milik Daerah Kantor Kecamatan:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kelompok Aset BMD</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria Regulasi</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Kendaraan Dinas Roda 2 & 4</td>
            <td>Seluruh kendaraan fisik ada dan laik jalan, pajak kendaraan aktif dibayarkan</td>
            <td>Permendagri No. 19/2016 tentang Pengelolaan BMD</td>
            <td>Pertahankan ketertiban pemeliharaan armada dinas</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Peralatan Kantor & Elektronik</td>
            <td>Sebagian komputer belum ditempel stiker barcode kode barang inventaris</td>
            <td>Pedoman Kodefikasi BMD</td>
            <td>Segera pasang stiker kodefikasi aset pada seluruh peralatan</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Tanah & Gedung Kantor Camat</td>
            <td>Tanah kantor kecamatan telah bersertifikat atas nama Pemkab Lampung Tengah</td>
            <td>Tertib Hukum dan Pengamanan Aset</td>
            <td>Pengamanan yuridis aset tanah terpenuhi dengan baik</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
            [
                'nama_template' => 'KKA Tata Kelola Kepegawaian, Disiplin dan Penyelenggaraan Trantibum - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
                'tujuan' => <<<'HTML'
<h2><strong>I. TUJUAN PENGUJIAN</strong></h2>
<p>Kertas Kerja Audit (KKA) ini bertujuan untuk:</p>
<ol>
    <li>Menilai tingkat kepatuhan dan disiplin jam kerja aparatur sipil negara (ASN) dan THL di lingkungan kantor kecamatan.</li>
    <li>Mengevaluasi kesesuaian uraian tugas (job description) dengan Surat Keputusan penetapan tugas dan fungsi jabatan staf kecamatan.</li>
    <li>Menguji efektivitas penyelenggaraan ketenteraman dan ketertiban umum (Trantibum) serta koordinasi Forum Koordinasi Pimpinan Kecamatan (Forkopimcam).</li>
    <li>Menilai kesiapsiagaan kecamatan dalam penanganan aduan gangguan ketertiban dan tanggap darurat bencana di tingkat wilayah.</li>
</ol>
HTML,
                'langkah' => <<<'HTML'
<h2><strong>II. LANGKAH KERJA AUDIT</strong></h2>
<ol>
    <li>Dapatkan rekapitulasi presensi kehadiran fingerprint bulanan seluruh pegawai kantor kecamatan Tahun Anggaran 2025.</li>
    <li>Lakukan analisis tingkat keterlambatan, izin, cuti, dan ketidakhadiran tanpa keterangan sah.</li>
    <li>Periksa dokumen SK pembagian tugas pegawai dan evaluasi beban kerja antar seksi di lingkungan kantor kecamatan.</li>
    <li>Periksa laporan berkala patroli ketentraman dan ketertiban umum serta buku register aduan gangguan trantibum warga.</li>
    <li>Konfirmasi dokumentasi rapat koordinasi berkala Forkopimcam bersama Kapolsek dan Danramil setempat.</li>
</ol>
HTML,
                'hasil' => <<<'HTML'
<h2><strong>III. HASIL PENGUJIAN & SIMPULAN</strong></h2>
<p>Hasil pengujian atas tata kelola kepegawaian dan penyelenggaraan trantibum kecamatan:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Aspek Kepegawaian & Trantibum</th>
            <th>Kondisi Faktual</th>
            <th>Kriteria Regulasi</th>
            <th>Simpulan & Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Tingkat Disiplin Jam Kerja ASN</td>
            <td>Rata-rata persentase kehadiran pegawai mencapai 94% dengan pemotongan TPP tertib</td>
            <td>PP No. 94/2021 tentang Disiplin PNS</td>
            <td>Pertahankan budaya kerja disiplin aparatur</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Pembagian Tugas Pegawai</td>
            <td>SK penugasan telah diterbitkan Camat dan masing-masing staf memahami tupoksinya</td>
            <td>Perbup Susunan Organisasi Tata Kerja Kecamatan</td>
            <td>Pola koordinasi internal berjalan efektif</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Patroli Trantibum & Koordinasi</td>
            <td>Seksi Trantib rutin patroli pasar dan tempat umum serta koordinasi berkala dengan Koramil/Polsek</td>
            <td>Perda Ketertiban Umum & Linmas</td>
            <td>Tingkatkan respons cepat atas laporan gangguan ketertiban masyarakat</td>
        </tr>
    </tbody>
</table>
HTML,
            ],
        ];

        foreach ($templates as $data) {
            TemplateKka::query()->updateOrCreate(
                [
                    'nama_template' => $data['nama_template'],
                    'lingkup' => $data['lingkup'],
                ],
                $data
            );
        }

        $this->command?->info('Berhasil membuat/memperbarui seeder Template KKA (Dinas & Kecamatan).');
    }
}
