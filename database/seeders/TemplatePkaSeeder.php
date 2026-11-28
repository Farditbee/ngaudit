<?php

namespace Database\Seeders;

use App\Models\TemplatePka;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplatePkaSeeder extends Seeder
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
            // --- TEMPLATE PKA LINGKUP DINAS ---
            [
                'nama_template' => 'PKA Audit Kinerja dan Operasional Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Program Kerja Audit (PKA) ini disusun sebagai pedoman kerja terstruktur bagi Tim Audit Inspektorat Kabupaten Lampung Tengah dalam melaksanakan pengawasan intern atas kinerja operasional dan ketaatan tata kelola pada Perangkat Daerah lingkup Dinas.</p>
<ul>
    <li>Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah.</li>
    <li>Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP).</li>
    <li>Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<p>Tujuan utama dari penugasan audit kinerja ini adalah untuk:</p>
<ol>
    <li>Menilai tingkat ketercapaian target kinerja program dan kegiatan pada Dokumen Pelaksanaan Anggaran (DPA).</li>
    <li>Menilai efisiensi dan kehematan dalam alokasi serta penggunaan sumber daya anggaran perangkat daerah.</li>
    <li>Mengidentifikasi kelemahan pengendalian intern serta merumuskan rekomendasi perbaikan tata kelola.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit mencakup pengujian dokumen pertanggungjawaban belanja operasional, program kerja strategis, penatausahaan aset, dan sistem pengendalian intern perangkat daerah.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Tercapainya akuntabilitas dan efektivitas belanja operasional perangkat daerah.</li>
    <li>Tertib administrasi dalam penatausahaan bukti pertanggungjawaban keuangan dan aset.</li>
    <li>Peningkatan kualitas kepatuhan terhadap regulasi pengelolaan keuangan daerah.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>15 (lima belas) hari kerja kalender</strong> sesuai dengan jadwal penugasan pada Surat Perintah Tugas.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 (Periode 1 Januari 2025 s.d. 31 Desember 2025).</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Demikian Program Kerja Audit ini disusun untuk dipedomani oleh seluruh anggota tim pemeriksa dalam menjalankan penugasan audit secara profesional, objektif, dan independen.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Kepatuhan Pengadaan Barang dan Jasa Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Audit Kepatuhan Pengadaan Barang dan Jasa (PBJ) dilaksanakan untuk memastikan proses pengadaan barang/jasa pemerintah pada Perangkat Daerah berjalan sesuai Perpres No. 16 Tahun 2018 jo. Perpres No. 12 Tahun 2021.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Menguji kepatuhan prosedur tender, e-katalog, dan pengadaan langsung terhadap regulasi PBJ.</li>
    <li>Memastikan kesesuaian volume, mutu, dan spesifikasi teknis hasil pekerjaan fisik di lapangan.</li>
    <li>Menghitung potensi keterlambatan, denda keterlambatan, dan kelebihan pembayaran termin kontrak.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Paket-paket pekerjaan konstruksi, pengadaan barang, dan jasa konsultansi pada Dinas Tahun Anggaran berjalan.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Kesesuaian hasil pekerjaan fisik dengan spesifikasi kontrak dan RAB.</li>
    <li>Kepatuhan penatausahaan jaminan pengadaan dan kelengkapan Berita Acara Serah Terima (BAST).</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>14 (empat belas) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 s.d. tanggal penugasan audit lapangan.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Auditor wajib mematuhi kode etik APIP dan Standar Audit Intern Pemerintah Indonesia (SAIPI).</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Khusus / Dengan Tujuan Tertentu (DTT) Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Audit Dengan Tujuan Tertentu (DTT) dilaksanakan sebagai tindak lanjut atas indikasi anomali pengelolaan keuangan, pengaduan masyarakat, atau arahan pimpinan daerah pada lingkungan dinas.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Memperoleh bukti audit yang cukup dan kompeten terkait kebenaran materiil pengaduan/indikasi.</li>
    <li>Mengidentifikasi pihak yang bertanggung jawab serta modus penyimpangan yang terjadi.</li>
    <li>Menghitung estimasi dampak kerugian daerah guna perumusan sanksi dan pemulihan keuangan.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Pengujian dokumen transaksi, wawancara para pihak, konfirmasi pihak ketiga, dan uji fisik lapangan terkait kasus yang diperiksa.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Klarifikasi dan verifikasi 100% sampel transaksi terkait pengaduan.</li>
    <li>Penyusunan Berita Acara Klarifikasi/Pemeriksaan yang berkekuatan hukum.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan secara intensif selama <strong>10 (sepuluh) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 s.d. periode berjalan.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Laporan Hasil Audit Khusus bersifat rahasia dan disampaikan berjenjang kepada Inspektur dan Bupati.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Tata Kelola dan Keuangan Dinas',
                'lingkup' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $dinasCreator,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Audit Tata Kelola dan Keuangan Dinas diarahkan untuk menguji akuntabilitas bendahara pengeluaran, kepatuhan penatausahaan kas, dan pertanggungjawaban belanja SKPD.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<ol>
    <li>Menguji keabsahan penatausahaan Buku Kas Umum (BKU) dan rekonsiliasi bank.</li>
    <li>Memverifikasi kelengkapan SPJ belanja modal, belanja barang/jasa, dan perjalanan dinas.</li>
    <li>Memastikan ketaatan pemungutan dan penyetoran pajak pusat dan daerah.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Pengelolaan kas, SPJ belanja, buku pembantu panjar, dan rekonsiliasi laporan keuangan dinas.</p>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<ul>
    <li>Tertib administrasi kas dan nihil selisih saldo kas fisik dengan BKU.</li>
    <li>Kepatuhan penyetoran pajak ke kas negara secara tepat waktu.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Audit dilaksanakan selama <strong>12 (dua belas) hari kerja kalender</strong>.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Tahun Anggaran 2025 (1 Januari s.d. 31 Desember 2025).</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Hasil audit disampaikan dalam bentuk KKA dan Laporan Hasil Pemeriksaan (LHP).</p>
HTML,
            ],

            // --- TEMPLATE PKA LINGKUP KECAMATAN ---
            [
                'nama_template' => 'PKA Audit Kinerja dan Pelayanan Administrasi Terpadu Kecamatan (PATEN)',
                'lingkup' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'created_by' => $kecamatanCreator,
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
                'created_by' => $kecamatanCreator,
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
                'created_by' => $kecamatanCreator,
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
                'created_by' => $kecamatanCreator,
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
                'created_by' => $kecamatanCreator,
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

        $this->command?->info('Berhasil membuat/memperbarui seeder Template PKA (Dinas & Kecamatan).');
    }
}
