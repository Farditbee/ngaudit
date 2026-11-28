<?php

namespace Database\Seeders;

use App\Models\TemplateKka;
use App\Models\TemplateLangkahKerja;
use App\Models\TemplateLangkahKerjaDetail;
use App\Models\TemplatePka;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateKecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::query()->where('role', 'Admin Kecamatan')->value('id')
            ?? User::query()->where('role', 'Super Admin')->value('id')
            ?? User::query()->value('id');

        // 1. Template PKA Khusus Kecamatan
        $this->seedTemplatePka($creator);

        // 2. Template KKA Khusus Kecamatan
        $this->seedTemplateKka($creator);

        // 3. Template Langkah Kerja Khusus Kecamatan
        $this->seedTemplateLangkahKerja($creator);

        $this->command?->info('Berhasil membuat seluruh Template PKA, KKA, dan Langkah Kerja khusus Lingkup Kecamatan.');
    }

    protected function seedTemplatePka(?int $creatorId): void
    {
        $templates = [
            [
                'nama_template' => 'PKA Audit Kinerja dan Pelayanan Administrasi Terpadu Kecamatan (PATEN)',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Pelayanan Administrasi Terpadu Kecamatan (PATEN) merupakan garda terdepan pelayanan publik pemerintah daerah di tingkat kecamatan. Program Kerja Audit (PKA) ini disusun sebagai pedoman pengawasan intern untuk menilai kinerja, mutu layanan, akuntabilitas, dan kepatuhan standar pelayanan pada kantor kecamatan.</p>
<ul>
    <li>Undang-Undang Nomor 25 Tahun 2009 tentang Pelayanan Publik.</li>
    <li>Peraturan Menteri Dalam Negeri Nomor 4 Tahun 2010 tentang Pedoman Pelayanan Administrasi Terpadu Kecamatan.</li>
    <li>Peraturan Bupati Lampung Tengah tentang Penyelenggaraan PATEN di Lingkungan Pemerintah Kabupaten.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Menilai efektivitas dan kecepatan penyelenggaraan pelayanan perizinan dan non-perizinan di loket PATEN kecamatan.</li>
    <li>Menguji transparansi persyaratan, ketiadaan pungutan liar, dan kepatuhan terhadap Standar Pelayanan Minimal (SPM).</li>
    <li>Mengevaluasi kecukupan sarana dan prasarana penunjang pelayanan publik serta pengelolaan aduan masyarakat.</li>
    <li>Memberikan rekomendasi perbaikan tata kelola loket pelayanan dan peningkatan indeks kepuasan masyarakat.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit mencakup seluruh berkas permohonan layanan administrasi kependudukan, perizinan skala mikro, buku register loket, sistem antrean, sarana front office, dan pelaporan bulanan PATEN Tahun Anggaran 2025.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Ketepatan waktu penerbitan dokumen pelayanan sesuai janji layanan (SOP).</li>
    <li>Akuntabilitas administrasi register penerimaan berkas dan penyerahan produk layanan.</li>
    <li>Kepuasan masyarakat terhadap kemudahan dan keramahan petugas loket kecamatan.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>12 (dua belas) hari kerja kalender</strong> terhitung sejak tanggal diterbitkannya Surat Perintah Tugas.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 (Periode 1 Januari 2025 s.d. 31 Desember 2025).</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>PKA ini wajib dipedomani oleh seluruh pemeriksa dalam melaksanakan audit kinerja PATEN secara profesional, objektif, dan independen demi terwujudnya tata kelola pelayanan kecamatan yang prima.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Kepatuhan Pembinaan dan Pengawasan Pengelolaan Keuangan Desa',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Camat memiliki kewajiban atributif untuk melakukan pembinaan dan pengawasan (Binwas) terhadap tata kelola pemerintahan kampung/desa, khususnya dalam pengelolaan Anggaran Pendapatan dan Belanja Desa (APBDes/APB-Kampung), Alokasi Dana Kampung (ADK), dan Dana Desa (DD).</p>
<ul>
    <li>Undang-Undang Nomor 6 Tahun 2014 tentang Desa beserta perubahannya.</li>
    <li>Peraturan Pemerintah Nomor 43 Tahun 2014 tentang Peraturan Pelaksanaan UU Desa.</li>
    <li>Peraturan Menteri Dalam Negeri Nomor 73 Tahun 2020 tentang Pengawasan Pengelolaan Keuangan Desa.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Menguji efektivitas peran tim fasilitasi kecamatan dalam evaluasi rancangan APB-Kampung dan verifikasi syarat pencairan dana.</li>
    <li>Memastikan keabsahan verifikasi dokumen Surat Pertanggungjawaban (SPJ) desa sebelum rekomendasi penyaluran diterbitkan.</li>
    <li>Menilai keteraturan dan bukti fisik pelaksanaan monitoring dan evaluasi (Monev) lapangan atas pembangunan fisik kampung.</li>
    <li>Mendorong akuntabilitas pembinaan aparatur kampung dan percepatan tindak lanjut temuan pembinaan.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit meliputi berkas verifikasi SPJ dana desa, dokumen rekomendasi pencairan tahap I - III, laporan monev fisik infrastruktur, dan berita acara pembinaan seksi PMD kecamatan pada kampung-kampung di wilayah kerjanya.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Kepatuhan jadwal verifikasi dan penerbitan rekomendasi pencairan APB-Kampung.</li>
    <li>Dokumentasi monev lapangan pembangunan fisik dan tertib administrasi arsip SPJ kampung.</li>
    <li>Mitigasi risiko penyimpangan dan sengketa keuangan desa sedini mungkin di tingkat kecamatan.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>15 (lima belas) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Pelaksanaan audit harus berpedoman pada kode etik APIP serta mengedepankan pembinaan konstruktif bagi jajaran pemerintahan kecamatan dan kampung.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Tata Kelola Keuangan dan SPJ Belanja Operasional Kantor Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Kantor Kecamatan sebagai Satuan Kerja Pengelola Anggaran (SKPD) bertanggung jawab atas pengelolaan anggaran belanja operasional kantor, pemeliharaan rutin, pembayaran gaji/tunjangan ASN, dan honorarium pegawai penunjang sesuai DPA Kecamatan.</p>
<ul>
    <li>Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.</li>
    <li>Peraturan Menteri Dalam Negeri Nomor 77 Tahun 2020 tentang Pedoman Teknis Pengelolaan Keuangan Daerah.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Memeriksa kebenaran saldo fisik kas pada brankas Bendahara Pengeluaran Kecamatan (cash opname).</li>
    <li>Menguji keabsahan bukti pengeluaran (kuitansi, nota, faktur, BAST) pada belanja barang/jasa dan belanja modal.</li>
    <li>Memastikan ketertiban rekonsiliasi bank dan ketepatan penutupan BKU secara berkala.</li>
    <li>Menguji kepatuhan pemotongan dan penyetoran pajak ke kas negara tepat waktu melalui e-Billing.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Buku Kas Umum, rekening koran bank, bukti kuitansi SPJ belanja ATK, makan minum rapat, BBM, honorarium kegiatan, dan pemeliharaan kantor kecamatan Tahun Anggaran 2025.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Nihil selisih kas fisik dengan pembukuan BKU dan buku pembantu kas tunai.</li>
    <li>Kelengkapan dan keabsahan 100% sampel bukti pertanggungjawaban belanja operasional.</li>
    <li>Tertib penyetoran pajak PPh dan PPN sebelum batas akhir pelaporan masa pajak.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>10 (sepuluh) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 (1 Januari s.d. 31 Desember 2025).</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Program kerja audit ini disusun untuk memastikan akuntabilitas tata kelola keuangan kantor kecamatan secara transparan dan tertib administrasi.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Penatausahaan, Pengamanan, dan Inventarisasi Aset BMD Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Pengamanan dan penatausahaan Barang Milik Daerah (BMD) di lingkungan kantor kecamatan, termasuk tanah, gedung kantor, rumah dinas jabatan camat, kendaraan bermotor dinas, dan peralatan operasional harus didukung pembukuan dan inventarisasi yang tertib.</p>
<ul>
    <li>Peraturan Pemerintah Nomor 27 Tahun 2014 jo. PP No. 28 Tahun 2020 tentang Pengelolaan Barang Milik Negara/Daerah.</li>
    <li>Peraturan Menteri Dalam Negeri Nomor 19 Tahun 2016 tentang Pedoman Pengelolaan BMD.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Memastikan keabsahan pencatatan aset tetap pada Kartu Inventaris Barang (KIB A, B, C, D, E) kantor kecamatan.</li>
    <li>Menguji keberadaan fisik, kelengkapan surat kendaraan (STNK/BPKB), dan status pemegang kendaraan dinas.</li>
    <li>Mengevaluasi kewajaran biaya belanja pemeliharaan rutin dan penggunaan BBM kendaraan dinas.</li>
    <li>Memeriksa kejelasan status hukum kepemilikan tanah dan bangunan kantor kecamatan.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup meliputi Daftar Barang Pengguna (DBP), KIB A (Tanah), KIB B (Peralatan dan Mesin/Kendaraan), KIB C (Gedung dan Bangunan), serta arsip berita acara peminjaman kendaraan dinas.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Kesesuaian 100% fisik kendaraan dinas dan peralatan kantor dengan buku inventaris.</li>
    <li>Pencegahan penguasaan kendaraan dinas oleh pihak yang tidak berhak.</li>
    <li>Pengamanan fisik dan yuridis atas seluruh aset tanah/gedung kantor kecamatan.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>10 (sepuluh) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Auditor menyusun KKA pengujian aset dan menyampaikan rekomendasi pengamanan aset yang bernilai guna bagi camat selaku pengguna barang.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Khusus / Dengan Tujuan Tertentu (DTT) Lingkup Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Pemeriksaan Khusus / Dengan Tujuan Tertentu (DTT) pada tingkat kecamatan dilaksanakan guna menindaklanjuti laporan aduan masyarakat, atensi pimpinan daerah, atau indikasi anomali pungutan retribusi dan penyalahgunaan wewenang di lingkungan kecamatan.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Memperoleh keyakinan dan bukti yang sah atas kebenaran fakta materiil dalam materi pengaduan.</li>
    <li>Mengidentifikasi pihak yang bertanggung jawab serta modus penyimpangan yang dilakukan.</li>
    <li>Menghitung estimasi dampak kerugian daerah atau pungutan tidak resmi yang terjadi.</li>
    <li>Merumuskan rekomendasi penegakan sanksi disiplin aparatur atau pelimpahan tindak lanjut.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Dokumen bukti transaksi, konfirmasi warga pemohon layanan, wawancara pejabat teknis kecamatan, dan berita acara klarifikasi.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Klarifikasi dan pembuktian fakta atas seluruh poin aduan masyarakat.</li>
    <li>Penyusunan Berita Acara Permintaan Keterangan (BAPK) yang valid dan objektif.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan secara mendalam selama <strong>10 (sepuluh) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 s.d. periode berjalan.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Laporan Hasil Audit Khusus bersifat rahasia dan disampaikan secara berjenjang kepada Inspektur Kabupaten Lampung Tengah.</p>
HTML,
            ],
        ];

        foreach ($templates as $data) {
            TemplatePka::query()->updateOrCreate(
                [
                    'nama_template' => $data['nama_template'],
                    'lingkup' => $data['lingkup'],
                ],
                $data
            );
        }
    }

    protected function seedTemplateKka(?int $creatorId): void
    {
        $templates = [
            [
                'nama_template' => 'KKA Evaluasi Kinerja Pelayanan Administrasi Terpadu Kecamatan (PATEN) - Kecamatan',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
    }

    protected function seedTemplateLangkahKerja(?int $creatorId): void
    {
        $templates = [
            [
                'nama_template' => 'Template LK Pelayanan Administrasi Terpadu Kecamatan (PATEN)',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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
                'created_by' => $creatorId,
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

            $template->details()->forceDelete();

            foreach ($details as $uraian) {
                TemplateLangkahKerjaDetail::query()->create([
                    'template_langkah_kerja_id' => $template->id,
                    'uraian' => $uraian,
                    'created_by' => $creatorId,
                ]);
            }

            $template->refreshJumlahLangkahKerja();
        }
    }
}
