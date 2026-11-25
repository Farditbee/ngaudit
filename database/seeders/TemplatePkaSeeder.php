<?php

namespace Database\Seeders;

use App\Models\Plotting;
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
        $plottingWil1 = Plotting::query()->where('plot', 'WIL I')->first()
            ?? Plotting::query()->firstOrCreate(['id' => 1], ['plot' => 'WIL I']);

        $creator = User::query()->where('role', 'Admin Nasional')->where('plotting_id', $plottingWil1->id)->first()
            ?? User::query()->where('role', 'Super Admin')->first()
            ?? User::query()->first();

        $templates = [
            [
                'nama_template' => 'PKA Audit Kinerja dan Operasional Wilayah I',
                'lingkup' => 'Nasional',
                'plotting_id' => $plottingWil1->id,
                'kode_wilayah' => null,
                'created_by' => $creator?->id,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Program Kerja Audit (PKA) ini disusun sebagai pedoman terstruktur bagi Tim Audit dalam melaksanakan pengawasan internal atas kinerja operasional dan ketaatan tata kelola pada satuan kerja lingkup <strong>Wilayah I</strong>. Pengawasan ini berlandaskan pada ketentuan perundang-undangan:</p>
<ul>
    <li>Undang-Undang Nomor 15 Tahun 2004 tentang Pemeriksaan Pengelolaan dan Tanggung Jawab Keuangan Negara.</li>
    <li>Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP).</li>
    <li>Surat Perintah Inspektur Jenderal mengenai Penugasan Audit Kinerja Wilayah I.</li>
</ul>
<p>Audit kinerja diarahkan untuk menilai kehematan, efisiensi, dan efektivitas (3E) atas pelaksanaan program dan kegiatan yang dibiayai APBN pada satuan kerja mitra.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<p>Tujuan utama dari penugasan audit kinerja ini adalah untuk:</p>
<ol>
    <li><strong>Menilai Tingkat Capaian Kinerja:</strong> Mengukur realisasi output dan outcome program terhadap target yang ditetapkan dalam Perjanjian Kinerja (PK) dan Rencana Kerja Tahunan (RKT).</li>
    <li><strong>Menilai Efisiensi Penggunaan Sumber Daya:</strong> Mengidentifikasi apakah alokasi sumber daya manusia, keuangan, dan peralatan telah digunakan secara optimal tanpa pemborosan.</li>
    <li><strong>Mengidentifikasi Hambatan dan Kendala:</strong> Menemukan akar permasalahan operasional serta memberikan rekomendasi korektif yang bernilai tambah <em>(value-added recommendation)</em> bagi manajemen.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit kinerja mencakup pengelolaan program dan kegiatan operasional pada unit kerja di bawah koordinasi Wilayah I dengan rincian fokus area:</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Fokus Area Audit</th>
            <th>Uraian Pengujian Dokumen / Fisik</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Perencanaan & Penganggaran</td>
            <td>Kesesuaian DIPA/POK dengan renja dan analisis standar biaya (ASB).</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Pelaksanaan Program</td>
            <td>Pengujian realisasi fisik vs keuangan serta pencapaian indikator kinerja utama.</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Tata Kelola Aset & BMN</td>
            <td>Pemanfaatan, pencatatan SIMAK-BMN, inventarisasi dan pemeliharaan barang milik negara.</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Sistem Pengendalian Intern</td>
            <td>Kecukupan SOP, pengendalian dokumen, dan pengawasan atasan langsung.</td>
        </tr>
    </tbody>
</table>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<p>Sasaran audit yang hendak dicapai meliputi:</p>
<ul>
    <li>Tercapainya akurasi data laporan capaian kinerja triwulanan dan semesteran minimal 95%.</li>
    <li>Kepatuhan terhadap batas waktu penyampaian pertanggungjawaban kegiatan operasional.</li>
    <li>Tertib administrasi dalam penatausahaan persediaan dan aset operasional lapangan.</li>
    <li>Tersusunnya daftar rekomendasi strategis perbaikan tata kelola bagi pimpinan satuan kerja.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU DAN TAHAPAN PELAKSANAAN</strong></h2>
<p>Audit kinerja dilaksanakan selama <strong>15 (lima belas) hari kerja kalender</strong> dengan pembagian tahapan sebagai berikut:</p>
<table>
    <thead>
        <tr>
            <th>Tahap</th>
            <th>Kegiatan Utama</th>
            <th>Durasi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Tahap 1</td>
            <td>Entry briefing, pengumpulan data awal, dan pemahaman proses bisnis auditee.</td>
            <td>Hari ke 1 - 3</td>
        </tr>
        <tr>
            <td>Tahap 2</td>
            <td>Uji petik dokumen, konfirmasi lapangan, wawancara, dan observasi fisik.</td>
            <td>Hari ke 4 - 10</td>
        </tr>
        <tr>
            <td>Tahap 3</td>
            <td>Penyusunan Temuan Sementara, exit meeting, dan penyusunan Laporan Hasil Audit (LHA).</td>
            <td>Hari ke 11 - 15</td>
        </tr>
    </tbody>
</table>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Periode audit yang diuji mencakup seluruh transaksi pelaksanaan kegiatan dan anggaran pada <strong>Tahun Anggaran 2026</strong> (berjalan) serta evaluasi komparatif atas tindak lanjut rekomendasi audit Tahun Anggaran sebelumnya.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>PKA ini disusun untuk dipedomani oleh seluruh personil Tim Audit Wilayah I, mulai dari Pengendali Teknis, Ketua Tim, hingga Anggota Tim Auditor. Apabila di lapangan dijumpai kondisi khusus atau indikasi penyimpangan signifikan yang memerlukan pendalaman, Tim Auditor wajib berkoordinasi dengan Pengendali Teknis untuk penyesuaian langkah kerja audit.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Kepatuhan Pengadaan Barang dan Jasa Wilayah I',
                'lingkup' => 'Nasional',
                'plotting_id' => $plottingWil1->id,
                'kode_wilayah' => null,
                'created_by' => $creator?->id,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Pengadaan Barang dan Jasa (PBJ) merupakan area yang memiliki risiko tinggi terhadap kepatuhan regulasi, efisiensi anggaran, dan potensi keterlambatan fisik. PKA Audit Kepatuhan ini ditujukan untuk memverifikasi proses pemilihan penyedia, penandatanganan kontrak, hingga serah terima hasil pekerjaan di satuan kerja <strong>Wilayah I</strong>.</p>
<p>Dasar hukum pelaksanaan audit:</p>
<ul>
    <li>Perpres Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah beserta perubahannya (Perpres No. 12 Tahun 2021).</li>
    <li>Peraturan LKPP terkait pedoman tata cara pengadaan dan e-purchasing.</li>
    <li>Rencana Pengawasan Tahunan (PKPT) Pengawasan PBJ Nasional.</li>
</ul>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<p>Tujuan audit kepatuhan PBJ ini adalah:</p>
<ol>
    <li>Memastikan keabsahan dan kewajaran penyusunan Harga Perkiraan Sendiri (HPS).</li>
    <li>Menilai transparansi dan ketaatan prosedur pemilihan penyedia (Tender / Seleksi / E-Purchasing / Penunjukan Langsung).</li>
    <li>Menguji kesesuaian kuantitas, kualitas, dan spesifikasi teknis barang/jasa yang diserahterimakan dengan klausul kontrak.</li>
    <li>Memastikan kelengkapan dokumen pendukung pembayaran sebelum dana dicairkan melalui SP2D.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit difokuskan pada paket pekerjaan PBJ strategis dan bernilai signifikan di Wilayah I:</p>
<table>
    <thead>
        <tr>
            <th>Tahap PBJ</th>
            <th>Fokus Pengujian Dokumen</th>
            <th>Metode Audit</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Perencanaan</td>
            <td>RUP, identifikasi kebutuhan, survei harga pasar dan kertas kerja kalkulasi HPS.</td>
            <td>Reviu dokumen & wawancara PPK</td>
        </tr>
        <tr>
            <td>Pemilihan</td>
            <td>Dokumen pemilihan, evaluasi kualifikasi, sanggah, dan penetapan pemenang.</td>
            <td>Analisis SPSE & cek keselarasan</td>
        </tr>
        <tr>
            <td>Kontrak & Fisik</td>
            <td>Surat Perjanjian, jaminan pelaksanaan, addendum, BAST, dan uji mutu lab/fisik.</td>
            <td>Pemeriksaan fisik langsung ke lokasi</td>
        </tr>
        <tr>
            <td>Pembayaran</td>
            <td>Kuitansi, faktur pajak, resume tagihan, dan bukti potong denda keterlambatan (jika ada).</td>
            <td>Vouching & penelusuran rekening koran</td>
        </tr>
    </tbody>
</table>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<p>Sasaran pemeriksaan kepatuhan PBJ:</p>
<ul>
    <li>Uji petik minimal 3 paket pekerjaan konstruksi/jasa lainnya dengan pagu di atas Rp 1.000.000.000,-.</li>
    <li>Uji kepatuhan atas paket pengadaan langsung dan e-katalog berkategori risiko sedang-tinggi.</li>
    <li>Memastikan tidak adanya pemecahan paket pekerjaan untuk menghindari tender.</li>
    <li>Menghitung potensi kelebihan bayar, kekurangan volume, atau keterlambatan tanpa pengenaan denda.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Penugasan berlangsung selama <strong>14 (empat belas) hari kerja</strong> dengan alokasi kegiatan:</p>
<ul>
    <li><strong>Hari ke-1 s/d 2:</strong> Telaah dokumen administrasi tender dan kontrak di kantor satker.</li>
    <li><strong>Hari ke-3 s/d 9:</strong> Pemeriksaan fisik lapangan (cek volume, fungsi teknis, dan spesifikasi material).</li>
    <li><strong>Hari ke-10 s/d 12:</strong> Konfirmasi dengan PPK, Pokja Pemilihan, Pejabat Pengadaan, dan Penyedia.</li>
    <li><strong>Hari ke-13 s/d 14:</strong> Perumusan simpulan, ekspose temuan, dan penandatanganan Berita Acara Hasil Audit (BAHA).</li>
</ul>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Paket-paket pekerjaan PBJ yang terkontrak dan dilaksanakan pada periode <strong>01 Januari 2026 sampai dengan tanggal berakhirnya penugasan lapangan</strong>.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Auditor wajib menjaga independensi dan profesionalisme sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI). Temuan audit harus didukung bukti audit yang cukup, kompeten, relevan, dan bernilai guna.</p>
HTML,
            ],
            [
                'nama_template' => 'PKA Audit Khusus / Dengan Tujuan Tertentu (DTT) Wilayah I',
                'lingkup' => 'Nasional',
                'plotting_id' => $plottingWil1->id,
                'kode_wilayah' => null,
                'created_by' => $creator?->id,
                'pendahuluan' => <<<'HTML'
<h2><strong>I. LATAR BELAKANG</strong></h2>
<p>Audit Dengan Tujuan Tertentu (DTT) / Audit Khusus ini dilaksanakan sebagai respons atas aduan masyarakat, atensi pimpinan kementerian, atau indikasi anomali transaksi keuangan berisiko tinggi pada unit kerja di <strong>Wilayah I</strong>.</p>
<p>Pelaksanaan audit ini berpedoman pada Kode Etik Auditor APIP dan Pedoman Penanganan Indikasi Fraud / Penyimpangan Keuangan Negara.</p>
HTML,
                'tujuan' => <<<'HTML'
<h2><strong>II. TUJUAN AUDIT</strong></h2>
<p>Tujuan audit khusus / DTT ini adalah:</p>
<ol>
    <li>Memperoleh keyakinan dan bukti yang sah mengenai kebenaran informasi pengaduan atau penyimpangan.</li>
    <li>Mengidentifikasi modus operandi, pihak-pihak yang bertanggung jawab, serta kelemahan pengendalian intern yang memungkinkan terjadinya anomali.</li>
    <li>Menghitung estimasi dampak finansial atau kerugian negara/daerah yang timbul.</li>
    <li>Merumuskan rekomendasi penegakan sanksi disiplin dan/atau pelimpahan proses ke ranah penegak hukum.</li>
</ol>
HTML,
                'ruang_lingkup' => <<<'HTML'
<h2><strong>III. RUANG LINGKUP AUDIT</strong></h2>
<p>Ruang lingkup audit mencakup pengujian forensik terhadap seluruh berkas dan transaksi yang relevan:</p>
<table>
    <thead>
        <tr>
            <th>Klaster Investigasi</th>
            <th>Cakupan Pengujian</th>
            <th>Indikator Risiko</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Aliran Dana & Kas</td>
            <td>Buku Kas Umum, rekening koran bendahara, serta mutasi transfer bank.</td>
            <td>Adanya transaksi tunai tanpa kuitansi resmi atau penarikan di luar jam kerja.</td>
        </tr>
        <tr>
            <td>Pertanggungjawaban Perjadin</td>
            <td>Surat Tugas, boarding pass, kuitansi hotel, dan laporan kegiatan perjalanan dinas.</td>
            <td>Perjalanan dinas ganda atau tiket fiktif.</td>
        </tr>
        <tr>
            <td>Honorarium & Bansos</td>
            <td>Daftar nominatif penerima, bukti transfer, dan konfirmasi langsung ke penerima manfaat.</td>
            <td>Pemotongan sepihak atau pemalsuan tanda tangan daftar hadir.</td>
        </tr>
    </tbody>
</table>
HTML,
                'sasaran' => <<<'HTML'
<h2><strong>IV. SASARAN AUDIT</strong></h2>
<p>Sasaran utama penugasan khusus ini adalah:</p>
<ul>
    <li>Verifikasi 100% sampel populasi transaksi yang masuk ke dalam objek pengaduan.</li>
    <li>Konfirmasi silang dengan pihak ketiga (maskapai, hotel, penyedia rekanan, perbankan).</li>
    <li>Penyusunan Berita Acara Permintaan Keterangan (BAPK) yang valid dan berkekuatan hukum.</li>
</ul>
HTML,
                'waktu_pelaksanaan' => <<<'HTML'
<h2><strong>V. WAKTU PELAKSANAAN</strong></h2>
<p>Pelaksanaan audit khusus berlangsung secara intensif selama <strong>10 (sepuluh) hari kerja</strong>, dan dapat diperpanjang atas persetujuan Inspektur Wilayah I apabila bukti audit belum mencukupi.</p>
HTML,
                'periode' => <<<'HTML'
<h2><strong>VI. PERIODE AUDIT</strong></h2>
<p>Mencakup periode audit transaksi dari <strong>Tahun Anggaran 2025 sampai dengan Semester I Tahun Anggaran 2026</strong> yang memiliki keterkaitan langsung dengan substansi pemeriksaan.</p>
HTML,
                'penutup' => <<<'HTML'
<h2><strong>VII. PENUTUP</strong></h2>
<p>Tim audit wajib mematuhi asas kerahasiaan informasi <em>(strict confidentiality)</em> selama audit berlangsung. Laporan Hasil Audit Khusus hanya disampaikan secara berjenjang kepada Inspektur Jenderal dan Pimpinan Lembaga.</p>
HTML,
            ],
        ];

        foreach ($templates as $data) {
            TemplatePka::query()->updateOrCreate(
                [
                    'nama_template' => $data['nama_template'],
                    'lingkup' => $data['lingkup'],
                    'plotting_id' => $data['plotting_id'],
                ],
                $data
            );
        }
    }
}
