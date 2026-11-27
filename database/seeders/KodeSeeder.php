<?php

namespace Database\Seeders;

use App\Models\Kode;
use App\Models\SubKode;
use Illuminate\Database\Seeder;

class KodeSeeder extends Seeder
{
    /**
     * Seed kode temuan (01-10) beserta sub kode temuan, serta kode sebab, akibat,
     * dan rekomendasi yang terikat pada masing-masing konteks kode temuan beserta sub kodenya.
     */
    public function run(): void
    {
        foreach ($this->temuanContexts() as $temuanKode => $context) {
            $temuan = Kode::query()->updateOrCreate(
                [
                    'jenis' => Kode::JENIS_TEMUAN,
                    'kode_temuan_id' => null,
                    'kode' => $temuanKode,
                ],
                ['nama' => $context['nama']],
            );

            foreach ($context['sub_temuan'] as $subKode => $subNama) {
                SubKode::query()->updateOrCreate(
                    ['kode_id' => $temuan->id, 'kode' => $subKode],
                    ['nama' => $subNama],
                );
            }

            foreach ([Kode::JENIS_SEBAB, Kode::JENIS_AKIBAT, Kode::JENIS_REKOMENDASI] as $jenis) {
                foreach ($context[$jenis] ?? [] as $kode => [$nama, $subKodes]) {
                    $parent = Kode::query()->updateOrCreate(
                        [
                            'jenis' => $jenis,
                            'kode_temuan_id' => $temuan->id,
                            'kode' => $kode,
                        ],
                        ['nama' => $nama],
                    );

                    foreach ($subKodes as $subKode => $subNama) {
                        SubKode::query()->updateOrCreate(
                            ['kode_id' => $parent->id, 'kode' => $subKode],
                            ['nama' => $subNama],
                        );
                    }
                }
            }
        }

        foreach ($this->tinjutData() as $kode => [$nama, $subKodes]) {
            $tinjut = Kode::query()->updateOrCreate(
                [
                    'jenis' => Kode::JENIS_TINJUT,
                    'kode_temuan_id' => null,
                    'kode' => $kode,
                ],
                ['nama' => $nama],
            );

            foreach ($subKodes as $subKode => $subNama) {
                SubKode::query()->updateOrCreate(
                    ['kode_id' => $tinjut->id, 'kode' => $subKode],
                    ['nama' => $subNama],
                );
            }
        }
    }

    /**
     * @return array<string, array{
     *     nama: string,
     *     sub_temuan: array<string, string>,
     *     sebab: array<string, array{0: string, 1: array<string, string>}>,
     *     akibat: array<string, array{0: string, 1: array<string, string>}>,
     *     rekomendasi: array<string, array{0: string, 1: array<string, string>}>,
     * }>
     */
    private function temuanContexts(): array
    {
        return [
            '01' => [
                'nama' => 'Kerugian Negara',
                'sub_temuan' => [
                    '01.01' => 'Belanja atau pengadaan barang/jasa fiktif',
                    '01.02' => 'Kelebihan pembayaran pekerjaan/pengadaan barang dan jasa',
                    '01.03' => 'Kekurangan volume pekerjaan fisik atau pengadaan barang',
                    '01.04' => 'Kekurangan penerimaan negara/daerah yang mengakibatkan kerugian',
                    '01.05' => 'Penggunaan anggaran tidak sesuai peruntukan yang merugikan keuangan negara',
                    '01.06' => 'Pembayaran honorarium, perjalanan dinas, atau tunjangan melebihi ketentuan',
                    '01.07' => 'Mark-up harga satuan barang/jasa pada kontrak pengadaan',
                    '01.08' => 'Barang yang dibeli tidak sesuai spesifikasi sehingga tidak dapat dimanfaatkan',
                    '01.09' => 'Aset atau barang milik negara hilang, rusak berat, atau dikuasai pihak ketiga',
                    '01.10' => 'Pembayaran ganda atas satu tagihan atau kegiatan yang sama',
                ],
                'sebab' => [
                    '01.S1' => ['Kelemahan Pengendalian dan Verifikasi Pembayaran', [
                        '01.S1.01' => 'PPK dan PPSPM tidak melakukan pengujian kebenaran material surat bukti tagihan',
                        '01.S1.02' => 'Pemeriksaan fisik hasil pekerjaan di lapangan tidak dilakukan secara cermat sebelum BAST',
                        '01.S1.03' => 'Bendahara pengeluaran melakukan pembayaran tanpa kelengkapan bukti yang sah',
                        '01.S1.04' => 'Verifikasi dokumen pertanggungjawaban perjalanan dinas dan honorarium lemah',
                    ]],
                    '01.S2' => ['Kelemahan Perencanaan Pengadaan dan Penyusunan HPS', [
                        '01.S2.01' => 'Penyusunan HPS tidak didukung survei harga pasar yang memadai dan mutakhir',
                        '01.S2.02' => 'Spesifikasi teknis disusun mengarah pada merek/penyedia tertentu dengan harga tinggi',
                        '01.S2.03' => 'Perhitungan volume kebutuhan pada RAB melebihi kebutuhan riil di lapangan',
                    ]],
                    '01.S3' => ['Wanprestasi atau Penyimpangan oleh Penyedia Barang/Jasa', [
                        '01.S3.01' => 'Penyedia mengurangi volume atau kualitas material pekerjaan konstruksi/pengadaan',
                        '01.S3.02' => 'Penyedia menyampaikan dokumen tagihan atau bukti pengeluaran yang tidak riil',
                        '01.S3.03' => 'Konsultan pengawas tidak melaksanakan pengawasan lapangan secara independen',
                    ]],
                    '01.S4' => ['Penyalahgunaan Wewenang dan Rekayasa Pertanggungjawaban', [
                        '01.S4.01' => 'Pejabat pengelola anggaran membuat pertanggungjawaban kegiatan fiktif',
                        '01.S4.02' => 'Adanya kolusi antara pejabat pengadaan/PPK dengan penyedia barang/jasa',
                        '01.S4.03' => 'Penggunaan aset atau dana kas untuk kepentingan pribadi/pihak lain tanpa hak',
                    ]],
                    '01.S5' => ['Kelalaian Pengamanan dan Pemeliharaan Aset Negara', [
                        '01.S5.01' => 'Pejabat pengurus barang tidak melakukan inventarisasi fisik aset secara berkala',
                        '01.S5.02' => 'Tidak terdapat pengamanan fisik dan dokumen kepemilikan atas aset bernilai tinggi',
                    ]],
                ],
                'akibat' => [
                    '01.A1' => ['Terjadinya Kerugian Keuangan Negara/Daerah Secara Nyata', [
                        '01.A1.01' => 'Kas negara/daerah berkurang secara tidak sah sebesar nilai belanja fiktif',
                        '01.A1.02' => 'Terjadi kelebihan pengeluaran negara atas selisih harga atau kekurangan volume pekerjaan',
                        '01.A1.03' => 'Kekayaan negara berkurang akibat hilangnya barang milik negara/daerah',
                    ]],
                    '01.A2' => ['Pemborosan Keuangan Negara dan Ketidakhematan Anggaran', [
                        '01.A2.01' => 'Biaya pengadaan barang/jasa jauh lebih mahal dibandingkan harga pasar wajar',
                        '01.A2.02' => 'Anggaran terserap namun barang/aset tidak memberikan nilai manfaat bagi satker',
                        '01.A2.03' => 'Alokasi anggaran untuk kegiatan prioritas lainnya tergerus oleh pembayaran tidak sah',
                    ]],
                    '01.A3' => ['Penurunan Kualitas Hasil Pekerjaan dan Risiko Hukum', [
                        '01.A3.01' => 'Konstruksi atau barang hasil pengadaan cepat rusak dan membahayakan pengguna',
                        '01.A3.02' => 'Timbulnya potensi tuntutan ganti rugi (TGR) dan proses hukum tindak pidana korupsi',
                    ]],
                    '01.A4' => ['Laporan Keuangan Satker Tidak Menyajikan Nilai Aset/Belanja Secara Wajar', [
                        '01.A4.01' => 'Nilai realisasi belanja modal dan belanja barang tercatat lebih tinggi dari nilai riil',
                        '01.A4.02' => 'Saldo aset tetap di neraca tidak mencerminkan keberadaan fisik aset yang sebenarnya',
                    ]],
                ],
                'rekomendasi' => [
                    '01.R1' => ['Penyetoran Pengembalian Kerugian Negara ke Kas Negara/Daerah', [
                        '01.R1.01' => 'Memerintahkan PPK/Bendahara/Pelaksana menyetorkan kembali kelebihan pembayaran ke Kas Negara',
                        '01.R1.02' => 'Menarik kembali pembayaran atas kekurangan volume pekerjaan dari penyedia dan menyetorkannya ke Kas Negara',
                        '01.R1.03' => 'Menyetorkan seluruh nilai belanja/perjalanan dinas fiktif ke rekening Kas Negara/Daerah',
                    ]],
                    '01.R2' => ['Pelaksanaan Proses Tuntutan Perbendaharaan dan Tuntutan Ganti Rugi (TP-TGR)', [
                        '01.R2.01' => 'Memproses Surat Keterangan Tanggung Jawab Mutlak (SKTJM) kepada pihak yang menghilangkan aset/dana',
                        '01.R2.02' => 'Membentuk Majelis Tuntutan Perbendaharaan/TGR untuk menetapkan pembebanan kerugian negara',
                        '01.R2.03' => 'Menarik dan mengamankan kembali aset negara yang dikuasai pihak yang tidak berhak',
                    ]],
                    '01.R3' => ['Perhitungan Ulang dan Pemotongan Pembayaran Termin Berikutnya', [
                        '01.R3.01' => 'Memperhitungkan kelebihan pembayaran dengan memotong pembayaran termin terakhir penyedia',
                        '01.R3.02' => 'Memerintahkan penyedia mengganti atau memperbaiki barang/pekerjaan sesuai spesifikasi kontrak',
                    ]],
                    '01.R4' => ['Pemberian Sanksi Tegas dan Penyerahan kepada Aparat Penegak Hukum', [
                        '01.R4.01' => 'Menjatuhkan sanksi disiplin berat kepada pejabat/pegawai yang terbukti melakukan rekayasa fiktif',
                        '01.R4.02' => 'Memasukkan penyedia barang/jasa yang melakukan kecurangan ke dalam Daftar Hitam (Blacklist)',
                        '01.R4.03' => 'Melimpahkan hasil pemeriksaan berindikasi tindak pidana korupsi kepada unit investigasi/APH',
                    ]],
                ],
            ],

            '02' => [
                'nama' => 'Kewajiban Setor Kepada Negara',
                'sub_temuan' => [
                    '02.01' => 'Denda keterlambatan penyelesaian pekerjaan belum dipungut atau belum disetor',
                    '02.02' => 'Pajak (PPN, PPh) atas belanja belum dipungut, dipotong, atau disetor ke kas negara',
                    '02.03' => 'Penerimaan Negara Bukan Pajak (PNBP) belum dipungut atau terlambat disetor',
                    '02.04' => 'Sisa Uang Persediaan (UP/TUP) atau sisa dana kegiatan belum disetor ke kas negara',
                    '02.05' => 'Jaminan pelaksanaan atas pemutusan kontrak belum dicairkan dan disetor',
                    '02.06' => 'Kelebihan pembayaran gaji, tunjangan kinerja, atau uang makan belum dikembalikan',
                    '02.07' => 'Bunga, jasa giro, atau hasil pemanfaatan BMN belum disetor ke kas negara',
                    '02.08' => 'Tindak lanjut rekomendasi penyetoran periode sebelumnya belum dilunasi',
                ],
                'sebab' => [
                    '02.S1' => ['Kelalaian Bendahara dan PPK dalam Pemungutan Pajak, PNBP, serta Denda', [
                        '02.S1.01' => 'PPK tidak mengenakan klausul denda keterlambatan saat penandatanganan BAST pekerjaan',
                        '02.S1.02' => 'Bendahara pengeluaran kurang teliti menghitung tarif potongan PPN dan PPh atas transaksi',
                        '02.S1.03' => 'Bendahara penerimaan menahan uang PNBP melebihi batas waktu penyetoran harian',
                    ]],
                    '02.S2' => ['Keterlambatan Pencairan Jaminan dan Penutupan Kas Akhir Tahun', [
                        '02.S2.01' => 'PPK tidak segera mengajukan klaim pencairan garansi bank sebelum masa berlaku berakhir',
                        '02.S2.02' => 'Sisa dana UP/TUP akhir tahun anggaran masih mengendap di rekening bendahara',
                        '02.S2.03' => 'Daftar perubahan data pegawai mutasi/pensiun terlambat diperbarui pada aplikasi gaji',
                    ]],
                    '02.S3' => ['Ketidakpatuhan Wajib Bayar atau Pihak Ketiga dalam Menyetor Kewajiban', [
                        '02.S3.01' => 'Mitra pemanfaatan BMN/sewa aset menunggak pembayaran kontribusi tahunan',
                        '02.S3.02' => 'Penyedia belum menyetorkan denda keterlambatan meskipun telah diterbitkan surat tagihan',
                    ]],
                ],
                'akibat' => [
                    '02.A1' => ['Penerimaan Kas Negara/Daerah Tertunda dan Belum Dapat Dimanfaatkan', [
                        '02.A1.01' => 'Dana hak negara dari denda, pajak, dan PNBP mengendap pada pihak ketiga atau bendahara',
                        '02.A1.02' => 'Realisasi penerimaan pajak dan PNBP satker pada tahun berjalan tidak mencapai target',
                        '02.A1.03' => 'Kas negara kehilangan likuiditas atas sisa UP/TUP yang terlambat dinihilkan',
                    ]],
                    '02.A2' => ['Risiko Kedaluwarsa Klaim Jaminan dan Piutang Tak Tertagih', [
                        '02.A2.01' => 'Jaminan pelaksanaan tidak dapat dicairkan karena telah melewati batas waktu klaim bank',
                        '02.A2.02' => 'Tunggakan kewajiban pihak ketiga berpotensi menjadi piutang macet',
                    ]],
                    '02.A3' => ['Ketidakakuratan Saldo Kas dan Utang Pajak pada Laporan Keuangan', [
                        '02.A3.01' => 'Saldo Kas di Bendahara Pengeluaran/Penerimaan pada neraca tidak mencerminkan hak negara',
                        '02.A3.02' => 'Timbul selisih rekonsiliasi setoran pajak dan PNBP dengan KPPN',
                    ]],
                ],
                'rekomendasi' => [
                    '02.R1' => ['Penagihan dan Penyetoran Segera Kewajiban ke Kas Negara', [
                        '02.R1.01' => 'Memungut dan menyetorkan denda keterlambatan pekerjaan ke Kas Negara disertai bukti NTPN',
                        '02.R1.02' => 'Memotong dan menyetorkan seluruh kekurangan pajak (PPN/PPh) melalui aplikasi e-Billing',
                        '02.R1.03' => 'Menyetorkan seluruh penerimaan PNBP, jasa giro, dan hasil sewa BMN ke rekening Kas Negara',
                        '02.R1.04' => 'Menihilkan dan menyetorkan sisa UP/TUP serta kelebihan gaji/tunjangan ke Kas Negara',
                    ]],
                    '02.R2' => ['Pencairan Jaminan Bank dan Penatausahaan Piutang Negara', [
                        '02.R2.01' => 'Mencairkan jaminan pelaksanaan atas kontrak yang diputus dan menyetorkannya ke Kas Negara',
                        '02.R2.02' => 'Menerbitkan surat tagihan berjenjang dan menyerahkan pengurusan piutang macet ke PUPN/KPKNL',
                    ]],
                    '02.R3' => ['Penguatan Rekonsiliasi Kas dan Pajak Secara Berkala', [
                        '02.R3.01' => 'Mewajibkan atasan langsung bendahara melakukan pemeriksaan kas dan buku pajak setiap bulan',
                        '02.R3.02' => 'Memutakhirkan daftar perubahan pegawai pada aplikasi gaji sebelum pengajuan SPM gaji bulanan',
                    ]],
                ],
            ],

            '03' => [
                'nama' => 'Pelanggaran Terhadap Peraturan Perundang-Undangan',
                'sub_temuan' => [
                    '03.01' => 'Pemecahan paket pengadaan barang/jasa untuk menghindari metode tender',
                    '03.02' => 'Penunjukan langsung penyedia tidak memenuhi kriteria keadaan tertentu/darurat',
                    '03.03' => 'Pelaksanaan kontrak dan adendum kontrak melanggar ketentuan pengadaan pemerintah',
                    '03.04' => 'Pemanfaatan atau pemindahtanganan BMN tanpa persetujuan Pengelola Barang',
                    '03.05' => 'Pelaksanaan perjalanan dinas tidak sesuai ketentuan standar biaya dan surat tugas',
                    '03.06' => 'Pemberian hibah atau bantuan sosial tidak memenuhi syarat kelayakan penerima',
                    '03.07' => 'Pengangkatan, mutasi, atau penugasan pegawai tidak sesuai ketentuan kepegawaian',
                    '03.08' => 'Pemungutan tarif layanan tidak memiliki dasar hukum Peraturan Pemerintah/PMK',
                    '03.09' => 'Penyimpangan terhadap standar teknis dan pedoman pelaksanaan program nasional',
                ],
                'sebab' => [
                    '03.S1' => ['Kurangnya Pemahaman dan Kepatuhan Pejabat terhadap Regulasi Teknis', [
                        '03.S1.01' => 'Pokja Pemilihan dan Pejabat Pengadaan belum memahami Perpres Pengadaan Barang/Jasa terbaru',
                        '03.S1.02' => 'Pejabat pengelola BMN tidak memedomani ketentuan tata cara pemanfaatan dan penghapusan aset',
                        '03.S1.03' => 'Terdapat kesalahan penafsiran atas ketentuan syarat penerima bantuan/hibah',
                    ]],
                    '03.S2' => ['Tindakan Mengabaikan Prosedur Resmi demi Percepatan Kegiatan', [
                        '03.S2.01' => 'Paket pekerjaan sengaja dipecah menjadi beberapa nilai pengadaan langsung agar lebih cepat',
                        '03.S2.02' => 'Adendum perubahan kontrak dilaksanakan di lapangan sebelum kajian teknis dan persetujuan PPK',
                        '03.S2.03' => 'Kerja sama pemanfaatan aset dengan pihak ketiga dilakukan tanpa izin Kementerian Keuangan',
                    ]],
                    '03.S3' => ['Belum Dilakukannya Sosialisasi dan Reviu Hukum atas Kebijakan/Kontrak', [
                        '03.S3.01' => 'Peraturan terbaru belum disosialisasikan kepada seluruh pelaksana teknis di unit kerja',
                        '03.S3.02' => 'Rancangan perjanjian/MoU dan keputusan strategis tidak melibatkan fungsi reviu bagian hukum',
                    ]],
                    '03.S4' => ['Lemahnya Pengawasan Kepatuhan oleh Atasan Langsung dan APIP', [
                        '03.S4.01' => 'KPA tidak mengevaluasi kepatuhan metode pemilihan penyedia dalam RUP',
                        '03.S4.02' => 'Verifikasi kelayakan administratif calon penerima hibah/bansos dilakukan secara formalitas',
                    ]],
                ],
                'akibat' => [
                    '03.A1' => ['Persaingan Usaha Tidak Sehat dan Tidak Diperolehnya Harga Terbaik', [
                        '03.A1.01' => 'Proses pemilihan penyedia tidak kompetitif dan menutup peluang penyedia lain yang memenuhi syarat',
                        '03.A1.02' => 'Satker tidak memperoleh harga pengadaan yang paling menguntungkan negara',
                    ]],
                    '03.A2' => ['Cacat Hukum atas Kontrak, Keputusan, atau Perjanjian Kerja Sama', [
                        '03.A2.01' => 'Kontrak atau perjanjian pemanfaatan BMN berisiko batal demi hukum atau digugat pihak ketiga',
                        '03.A2.02' => 'Pungutan layanan tanpa dasar hukum berpotensi dianggap sebagai pungutan tidak resmi',
                        '03.A2.03' => 'Penyaluran hibah/bantuan sosial menjadi tidak tepat sasaran',
                    ]],
                    '03.A3' => ['Sanksi Administratif dan Menurunnya Kredibilitas Kepatuhan Instansi', [
                        '03.A3.01' => 'Pejabat terkait berisiko dikenakan sanksi administratif atas ketidakpatuhan terhadap undang-undang',
                        '03.A3.02' => 'Menjadi temuan ketidakpatuhan material dalam opini pemeriksaan laporan keuangan',
                    ]],
                ],
                'rekomendasi' => [
                    '03.R1' => ['Penertiban Proses Pengadaan dan Pengelolaan BMN Sesuai Regulasi', [
                        '03.R1.01' => 'Memerintahkan KPA dan Pokja Pemilihan menyusun paket pengadaan pada RUP sesuai ketentuan tender',
                        '03.R1.02' => 'Mengajukan permohonan persetujuan pemanfaatan/sewa BMN kepada Pengelola Barang (Kemenkeu)',
                        '03.R1.03' => 'Meninjau ulang dan melakukan adendum perjanjian kerja sama agar selaras dengan peraturan',
                    ]],
                    '03.R2' => ['Penghentian Praktik yang Tidak Memiliki Dasar Hukum', [
                        '03.R2.01' => 'Menghentikan pemungutan tarif layanan yang belum diatur dalam PP/PMK tentang jenis dan tarif PNBP',
                        '03.R2.02' => 'Membatalkan atau merevisi surat keputusan penugasan/hibah yang bertentangan dengan ketentuan',
                    ]],
                    '03.R3' => ['Sosialisasi Regulasi dan Pemberian Teguran Tertulis', [
                        '03.R3.01' => 'Memberikan teguran tertulis kepada PPK/Pejabat Pengadaan agar mematuhi regulasi pengadaan',
                        '03.R3.02' => 'Menyelenggarakan bimbingan teknis dan sosialisasi peraturan perundang-undangan terbaru',
                        '03.R3.03' => 'Melibatkan fungsi hukum/kepatuhan internal sebelum penandatanganan kontrak dan MoU strategis',
                    ]],
                ],
            ],

            '04' => [
                'nama' => 'Kelemahan Sistem Pengendalian Intern',
                'sub_temuan' => [
                    '04.01' => 'Lingkungan pengendalian dan penegakan integritas di unit kerja belum memadai',
                    '04.02' => 'Identifikasi, analisis, dan mitigasi risiko kegiatan belum dilaksanakan',
                    '04.03' => 'Kegiatan pengendalian dan otorisasi berjenjang tidak berjalan efektif',
                    '04.04' => 'Sistem informasi dan komunikasi pengendalian belum terintegrasi',
                    '04.05' => 'Pemantauan berkelanjutan atas efektivitas pengendalian intern masih lemah',
                    '04.06' => 'Standar Operasional Prosedur (SOP) belum disusun atau belum dimutakhirkan',
                    '04.07' => 'Pemisahan tugas dan fungsi (segregation of duties) tidak memadai',
                ],
                'sebab' => [
                    '04.S1' => ['Komitmen Pimpinan dalam Penerapan SPIP Belum Optimal', [
                        '04.S1.01' => 'Pimpinan unit kerja belum menetapkan kebijakan manajemen risiko dan peta risiko satker',
                        '04.S1.02' => 'Satgas SPIP tingkat satuan kerja belum dibentuk atau tidak aktif melakukan evaluasi',
                        '04.S1.03' => 'Budaya sadar risiko belum terbangun pada level operasional',
                    ]],
                    '04.S2' => ['Desain Prosedur Kerja dan Otorisasi Belum Mengatur Titik Kritis', [
                        '04.S2.01' => 'SOP yang ada hanya bersifat umum dan tidak memuat daftar simak (checklist) pengendalian',
                        '04.S2.02' => 'Fungsi pengajuan, verifikasi, dan pencatatan dilakukan oleh satu pejabat/petugas yang sama',
                        '04.S2.03' => 'Akses otorisasi sistem aplikasi tidak dibatasi sesuai jenjang kewenangan jabatan',
                    ]],
                    '04.S3' => ['Minimnya Evaluasi Berkala atas Keandalan Pengendalian Intern', [
                        '04.S3.01' => 'Atasan langsung jarang melakukan uji petik atau inspeksi mendadak atas kepatuhan SOP',
                        '04.S3.02' => 'Kelemahan pengendalian yang ditemukan tahun sebelumnya belum diperbaiki desainnya',
                    ]],
                ],
                'akibat' => [
                    '04.A1' => ['Tingginya Kerentanan terhadap Kesalahan Operasional dan Penyimpangan', [
                        '04.A1.01' => 'Kesalahan proses kerja tidak terdeteksi secara dini pada tahap verifikasi berjenjang',
                        '04.A1.02' => 'Membuka celah terjadinya penyalahgunaan wewenang akibat tidak adanya pemisahan fungsi',
                        '04.A1.03' => 'Risiko strategis dan operasional satker terjadi tanpa langkah mitigasi yang siap',
                    ]],
                    '04.A2' => ['Pelaksanaan Tugas Tidak Seragam dan Kurang Akuntabel', [
                        '04.A2.01' => 'Pelaksana menjalankan proses kerja berdasarkan kebiasaan masing-masing tanpa standar baku',
                        '04.A2.02' => 'Jejak audit (audit trail) atas persetujuan dan perubahan data sulit ditelusuri',
                    ]],
                    '04.A3' => ['Tingkat Maturitas SPIP Unit Kerja Rendah', [
                        '04.A3.01' => 'Capaian nilai maturitas SPIP dan Manajemen Risiko Indeks (MRI) berada di bawah target',
                        '04.A3.02' => 'Keandalan pelaporan dan pengamanan aset organisasi menjadi lemah',
                    ]],
                ],
                'rekomendasi' => [
                    '04.R1' => ['Penyusunan dan Pemutakhiran SOP serta Peta Risiko Satker', [
                        '04.R1.01' => 'Menyusun dan menetapkan SOP tertulis yang dilengkapi alur otorisasi dan checklist kontrol',
                        '04.R1.02' => 'Menyusun register risiko dan rencana tindak pengendalian (RTP) secara komprehensif',
                        '04.R1.03' => 'Memutakhirkan SOP lama agar sesuai dengan perubahan proses bisnis digital',
                    ]],
                    '04.R2' => ['Penataan Pemisahan Fungsi dan Pembatasan Hak Akses', [
                        '04.R2.01' => 'Memisahkan secara tegas fungsi inisiasi transaksi, fungsi verifikasi, dan fungsi pencatatan',
                        '04.R2.02' => 'Menertibkan hak akses pengguna pada sistem aplikasi sesuai kewenangan jabatan masing-masing',
                    ]],
                    '04.R3' => ['Penguatan Peran Satgas SPIP dan Pemantauan Berkelanjutan', [
                        '04.R3.01' => 'Mengaktifkan Satgas SPIP untuk melakukan pemantauan triwulanan atas pelaksanaan RTP',
                        '04.R3.02' => 'Mewajibkan atasan langsung mendokumentasikan hasil reviu berjenjang secara berkala',
                    ]],
                ],
            ],

            '05' => [
                'nama' => 'Pelanggaran Disiplin Anggaran',
                'sub_temuan' => [
                    '05.01' => 'Pelaksanaan kegiatan atau pengikatan kontrak tanpa dukungan alokasi anggaran (DIPA)',
                    '05.02' => 'Realisasi pengeluaran melampaui pagu anggaran yang ditetapkan',
                    '05.03' => 'Pergeseran atau revisi anggaran dilaksanakan tanpa persetujuan pejabat berwenang',
                    '05.04' => 'Pembebanan belanja pada kode akun mata anggaran (MAK) yang tidak tepat',
                    '05.05' => 'Penyerapan anggaran menumpuk pada akhir tahun dan tidak sesuai rencana penarikan dana',
                    '05.06' => 'Pencairan atau pengeluaran anggaran dilakukan sebelum dokumen anggaran disahkan',
                    '05.07' => 'Pertanggungjawaban realisasi anggaran tidak sesuai dengan substansi kegiatan pada RKA-K/L',
                ],
                'sebab' => [
                    '05.S1' => ['Penyusunan RKA-K/L dan Bagan Akun Standar Tidak Cermat', [
                        '05.S1.01' => 'Perencana anggaran salah memilih klasifikasi akun belanja barang, modal, atau pemeliharaan',
                        '05.S1.02' => 'Kebutuhan biaya kegiatan rutin tidak dihitung secara lengkap saat penyusunan RKA-K/L',
                        '05.S1.03' => 'Rencana Penarikan Dana (RPD) bulanan disusun tanpa mempertimbangkan jadwal riil kegiatan',
                    ]],
                    '05.S2' => ['Pelaksanaan Kegiatan Mendahului Proses Revisi DIPA/POK', [
                        '05.S2.01' => 'PPK menandatangani kontrak atau memesan barang sebelum usulan revisi POK disetujui',
                        '05.S2.02' => 'Penggunaan sisa anggaran akun lain untuk menutup kegiatan di luar DIPA tanpa revisi resmi',
                    ]],
                    '05.S3' => ['Kelemahan Pengendalian Ketersediaan Pagu Anggaran oleh PPK dan PPSPM', [
                        '05.S3.01' => 'Kartu pengawasan kredit anggaran per mata anggaran tidak dipantau sebelum penerbitan SPP/SPM',
                        '05.S3.02' => 'Pejabat pengelola memaksakan pembebanan pada akun yang masih memiliki sisa pagu meskipun tidak relevan',
                    ]],
                ],
                'akibat' => [
                    '05.A1' => ['Salah Saji Klasifikasi Belanja pada Laporan Realisasi Anggaran (LRA)', [
                        '05.A1.01' => 'Belanja modal tercatat sebagai belanja barang sehingga aset tetap tidak terbentuk secara otomatis',
                        '05.A1.02' => 'Informasi realisasi belanja per jenis akun dalam LRA menjadi tidak akurat',
                    ]],
                    '05.A2' => ['Timbulnya Tunggakan Tagihan Pihak Ketiga dan Deviasi IKPA', [
                        '05.A2.01' => 'Timbul utang/tunggakan kepada penyedia karena kegiatan tidak terdukung pagu DIPA',
                        '05.A2.02' => 'Nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) satker menurun akibat deviasi halaman III DIPA',
                        '05.A2.03' => 'Penumpukan pencairan di akhir tahun menurunkan kualitas pengawasan hasil pekerjaan',
                    ]],
                    '05.A3' => ['Tertolaknya Pengajuan SPM atau Teguran dari Otoritas Perbendaharaan', [
                        '05.A3.01' => 'Pengajuan SPM tertolak di KPPN karena pagu minus atau ketidaksesuaian akun',
                        '05.A3.02' => 'Satker mendapat catatan ketidaktaatan disiplin anggaran dalam reviu pelaksanaan anggaran',
                    ]],
                ],
                'rekomendasi' => [
                    '05.R1' => ['Revisi Dokumen Anggaran (DIPA/POK) dan Koreksi Akun Belanja', [
                        '05.R1.01' => 'Melakukan ralat SPM/SP2D ke KPPN atas kesalahan pembebanan kode akun belanja (MAK)',
                        '05.R1.02' => 'Mengajukan revisi DIPA/POK sesuai prosedur sebelum melaksanakan pergeseran kegiatan',
                        '05.R1.03' => 'Melakukan jurnal penyesuaian akuntansi atas salah klasifikasi belanja barang dan belanja modal',
                    ]],
                    '05.R2' => ['Penguatan Kepatuhan Penyusunan RKA-K/L dan Pengawasan Pagu', [
                        '05.R2.01' => 'Mewajibkan reviu kesesuaian Bagan Akun Standar (BAS) pada saat penyusunan RKA-K/L',
                        '05.R2.02' => 'Melarang PPK melakukan pengikatan kontrak atau kegiatan yang belum tersedia pagunya dalam DIPA',
                        '05.R2.03' => 'Menertibkan pemantauan kartu pengawasan pagu anggaran secara real-time pada aplikasi SAKTI',
                    ]],
                    '05.R3' => ['Penyelarasan Jadwal Pelaksanaan Kegiatan dengan Rencana Penarikan Dana', [
                        '05.R3.01' => 'Memutakhirkan Halaman III DIPA setiap triwulan agar selaras dengan jadwal pengadaan dan kegiatan',
                        '05.R3.02' => 'Melaksanakan evaluasi penyerapan anggaran bulanan untuk mencegah penumpukan di akhir tahun',
                    ]],
                ],
            ],

            '06' => [
                'nama' => 'Kelemahan Administrasi dan Tata Kelola Dokumen',
                'sub_temuan' => [
                    '06.01' => 'Berkas dokumen pertanggungjawaban kegiatan tidak lengkap atau belum ditandatangani',
                    '06.02' => 'Pencatatan, pengklasifikasian, dan pengarsipan dokumen tidak tertib',
                    '06.03' => 'Dokumen perencanaan dan kerangka acuan kerja (KAK/TOR) tidak disusun secara lengkap',
                    '06.04' => 'Laporan pelaksanaan kegiatan disampaikan terlambat atau tidak sesuai format baku',
                    '06.05' => 'Penomoran, penanggalan, dan buku register surat/keputusan tidak tertib',
                    '06.06' => 'Dokumen kontrak, berita acara, atau perjanjian kerja sama tidak diadministrasikan dengan lengkap',
                ],
                'sebab' => [
                    '06.S1' => ['Ketidakdisiplinan Pelaksana dalam Pemberkasan dan Tata Naskah Dinas', [
                        '06.S1.01' => 'Pelaksana kegiatan menunda pengumpulan bukti pendukung hingga kegiatan selesai',
                        '06.S1.02' => 'Petugas tata usaha tidak mengikuti pedoman Tata Naskah Dinas dalam penomoran surat dan SK',
                        '06.S1.03' => 'Pejabat penandatangan belum membubuhkan tanda tangan pada berita acara atau daftar hadir',
                    ]],
                    '06.S2' => ['Belum Tersedia Sistem Pengarsipan Terpusat dan Daftar Kelengkapan Berkas', [
                        '06.S2.01' => 'Dokumen disimpan terpencar pada masing-masing staf pelaksana tanpa arsip sentral',
                        '06.S2.02' => 'Tidak digunakan lembar kendali (checklist) kelengkapan dokumen sebelum berkas diarsipkan',
                        '06.S2.03' => 'Belum dilakukan digitalisasi arsip dokumen penting sehingga rawan tercecer',
                    ]],
                    '06.S3' => ['Serah Terima Dokumen saat Rotasi/Mutasi Pegawai Tidak Tertib', [
                        '06.S3.01' => 'Pegawai yang mutasi tidak membuat berita acara serah terima berkas pekerjaan kepada penggantinya',
                        '06.S3.02' => 'File dokumen elektronik tersimpan di perangkat pribadi pegawai tanpa cadangan di server unit',
                    ]],
                ],
                'akibat' => [
                    '06.A1' => ['Kesulitan Penelusuran Bukti Audit dan Verifikasi Pertanggungjawaban', [
                        '06.A1.01' => 'Proses pemeriksaan dan reviu terhambat karena dokumen sumber lama ditemukan atau hilang',
                        '06.A1.02' => 'Keabsahan administratif pelaksanaan kegiatan diragukan akibat dokumen belum ditandatangani',
                    ]],
                    '06.A2' => ['Risiko Kehilangan Dokumen Penting dan Ketidaktertiban Hukum Administrasi', [
                        '06.A2.01' => 'Terdapat penomoran surat/kontrak ganda atau tanggal mundur (backdate) yang menimbulkan kerancuan',
                        '06.A2.02' => 'Arsip kontrak dan aset berisiko hilang saat terjadi pergantian pejabat/staf',
                    ]],
                    '06.A3' => ['Keterlambatan Penyusunan Laporan dan Pengambilan Keputusan', [
                        '06.A3.01' => 'Pimpinan terlambat memperoleh laporan berkala sebagai bahan evaluasi kebijakan',
                        '06.A3.02' => 'Data historis kegiatan sulit direkapitulasi untuk perencanaan tahun berikutnya',
                    ]],
                ],
                'rekomendasi' => [
                    '06.R1' => ['Melengkapi dan Menertibkan Seluruh Dokumen Pertanggungjawaban', [
                        '06.R1.01' => 'Melengkapi berkas pertanggungjawaban, KAK, daftar hadir, dan tanda tangan pejabat berwenang',
                        '06.R1.02' => 'Menertibkan buku agenda/register penomoran surat, SK, dan kontrak sesuai Tata Naskah Dinas',
                        '06.R1.03' => 'Menyusun dan menyampaikan laporan kegiatan yang tertunda sesuai format dan jadwal baku',
                    ]],
                    '06.R2' => ['Penataan Sistem Kearsipan Terpusat dan Digitalisasi Dokumen', [
                        '06.R2.01' => 'Menerapkan lembar kendali kelengkapan berkas (checklist) pada setiap tahapan kegiatan',
                        '06.R2.02' => 'Menata arsip fisik secara terpusat dan melakukan pemindaian (digitalisasi) arsip penting',
                        '06.R2.03' => 'Mewajibkan berita acara serah terima dokumen dan arsip setiap terjadi mutasi/rotasi pegawai',
                    ]],
                ],
            ],

            '07' => [
                'nama' => 'Kelemahan Kelembagaan dan Organisasi',
                'sub_temuan' => [
                    '07.01' => 'Struktur organisasi dan tata kerja belum selaras dengan beban serta fungsi terkini',
                    '07.02' => 'Uraian tugas pokok dan fungsi antar unit kerja belum jelas atau tumpang tindih',
                    '07.03' => 'Koordinasi lintas unit kerja maupun dengan instansi terkait tidak berjalan efektif',
                    '07.04' => 'Pendelegasian wewenang dan tanggung jawab belum ditetapkan secara tertulis',
                    '07.05' => 'Tim kerja, komite, atau gugus tugas yang dibentuk tidak berfungsi optimal',
                ],
                'sebab' => [
                    '07.S1' => ['Belum Dilakukannya Evaluasi Kelembagaan dan Penajaman Tupoksi', [
                        '07.S1.01' => 'Peraturan organisasi dan tata kerja (OTK) belum disesuaikan dengan perubahan mandat/proses bisnis',
                        '07.S1.02' => 'Penjabaran rincian tugas sampai tingkat sub-unit belum dituangkan dalam pedoman kerja',
                    ]],
                    '07.S2' => ['Mekanisme Koordinasi dan Pendelegasian Wewenang Belum Diatur Baku', [
                        '07.S2.01' => 'Tidak terdapat forum koordinasi berkala atau protokol berbagi data antar bagian',
                        '07.S2.02' => 'Penunjukan pelaksana tugas/harian atau delegasi kewenangan hanya diberikan secara lisan',
                    ]],
                    '07.S3' => ['Pembentukan Tim Kerja Hanya Bersifat Formalitas Administratif', [
                        '07.S3.01' => 'SK pembentukan tim tidak disertai rencana kerja, target output, dan mekanisme pelaporan',
                        '07.S3.02' => 'Anggota tim yang ditunjuk tidak dilibatkan secara aktif dalam pelaksanaan tugas tim',
                    ]],
                ],
                'akibat' => [
                    '07.A1' => ['Tumpang Tindih Kewenangan atau Kekosongan Penanggung Jawab Kegiatan', [
                        '07.A1.01' => 'Dua unit melaksanakan kegiatan serupa sementara tugas lintas fungsi lainnya tidak tertangani',
                        '07.A1.02' => 'Terjadi saling lempar tanggung jawab ketika muncul permasalahan operasional',
                    ]],
                    '07.A2' => ['Kelambatan Pengambilan Keputusan dan Sinkronisasi Program', [
                        '07.A2.01' => 'Proses layanan dan persetujuan terhambat saat pejabat definitif berhalangan hadir',
                        '07.A2.02' => 'Program lintas unit berjalan sendiri-sendiri (silo) dan tidak saling mendukung',
                        '07.A2.03' => 'Tim kerja yang dibentuk tidak menghasilkan output sesuai tujuan pembentukannya',
                    ]],
                ],
                'rekomendasi' => [
                    '07.R1' => ['Penataan Uraian Tugas dan Pendelegasian Wewenang Secara Tertulis', [
                        '07.R1.01' => 'Menetapkan penegasan rincian tugas dan batas kewenangan masing-masing unit kerja secara tertulis',
                        '07.R1.02' => 'Menerbitkan surat pendelegasian wewenang yang jelas guna menjamin kesinambungan layanan',
                        '07.R1.03' => 'Mengusulkan evaluasi dan penyesuaian struktur organisasi sesuai kebutuhan beban tugas',
                    ]],
                    '07.R2' => ['Penguatan Mekanisme Koordinasi dan Efektivitas Tim Kerja', [
                        '07.R2.01' => 'Menetapkan mekanisme rapat koordinasi berkala lintas unit beserta pemantauan tindak lanjutnya',
                        '07.R2.02' => 'Mengevaluasi tim/satgas yang dibentuk serta mewajibkan penyusunan rencana kerja dan laporan kinerja tim',
                    ]],
                ],
            ],

            '08' => [
                'nama' => 'Kelemahan Sumber Daya Manusia',
                'sub_temuan' => [
                    '08.01' => 'Jumlah dan distribusi pegawai tidak sebanding dengan analisis beban kerja',
                    '08.02' => 'Kompetensi teknis dan manajerial pegawai belum memadai untuk melaksanakan tugas',
                    '08.03' => 'Pejabat/pelaksana belum memiliki sertifikasi keahlian yang dipersyaratkan regulasi',
                    '08.04' => 'Penempatan pegawai tidak sesuai dengan latar belakang pendidikan dan kualifikasi jabatan',
                    '08.05' => 'Program pembinaan, pelatihan, dan pengembangan kompetensi pegawai belum optimal',
                    '08.06' => 'Beban rangkap jabatan berlebih pada personil tertentu sehingga menurunkan fokus kerja',
                ],
                'sebab' => [
                    '08.S1' => ['Perencanaan Kebutuhan Pegawai dan Analisis Beban Kerja Belum Akurat', [
                        '08.S1.01' => 'Dokumen Analisis Jabatan (Anjab) dan Analisis Beban Kerja (ABK) belum dimutakhirkan',
                        '08.S1.02' => 'Distribusi pegawai belum mempertimbangkan volume pekerjaan riil antar unit/wilayah',
                    ]],
                    '08.S2' => ['Keterbatasan Pelatihan Teknis dan Sertifikasi Profesi Wajib', [
                        '08.S2.01' => 'Pegawai belum diikutsertakan dalam diklat dan ujian sertifikasi PBJ/Bendahara/Auditor',
                        '08.S2.02' => 'Pemetaan kesenjangan kompetensi (competency gap) pegawai belum dilakukan secara sistematis',
                        '08.S2.03' => 'Mutasi pegawai dilakukan tanpa pembekalan teknis pada jabatan baru',
                    ]],
                    '08.S3' => ['Penumpukan Penugasan pada Pegawai Tertentu', [
                        '08.S3.01' => 'Terbatasnya pegawai bersertifikat menyebabkan satu orang merangkap banyak jabatan pengelola',
                        '08.S3.02' => 'Belum adanya kaderisasi dan alih pengetahuan (knowledge sharing) di internal unit kerja',
                    ]],
                ],
                'akibat' => [
                    '08.A1' => ['Kualitas Hasil Pekerjaan Kurang Optimal dan Keterlambatan Penyelesaian Tugas', [
                        '08.A1.01' => 'Tingginya tingkat kesalahan teknis dalam pelaksanaan tugas akibat kurangnya kompetensi',
                        '08.A1.02' => 'Penyelesaian pekerjaan melampaui batas waktu karena beban kerja pegawai tidak proporsional',
                    ]],
                    '08.A2' => ['Ketidakpatuhan Persyaratan Jabatan Fungsional/Pengelola', [
                        '08.A2.01' => 'Penunjukan PPK, Pokja, atau Bendahara belum memenuhi syarat kepemilikan sertifikat kompetensi wajib',
                        '08.A2.02' => 'Ketergantungan tinggi pada personil tertentu yang merangkap banyak tugas strategis',
                    ]],
                    '08.A3' => ['Menurunnya Produktivitas dan Motivasi Kerja Pegawai', [
                        '08.A3.01' => 'Kelelahan kerja (burnout) pada pegawai yang memikul beban tugas berlebih',
                        '08.A3.02' => 'Kinerja organisasi tidak berkembang optimal karena kapasitas SDM stagnan',
                    ]],
                ],
                'rekomendasi' => [
                    '08.R1' => ['Penataan Distribusi Pegawai Berdasarkan Anjab dan ABK', [
                        '08.R1.01' => 'Memutakhirkan dokumen Anjab dan ABK sebagai dasar redistribusi dan pengusulan formasi pegawai',
                        '08.R1.02' => 'Menata ulang penempatan pegawai agar sesuai dengan kualifikasi pendidikan dan kompetensi jabatan',
                        '08.R1.03' => 'Mengurangi rangkap jabatan dengan mendistribusikan penugasan secara proporsional',
                    ]],
                    '08.R2' => ['Peningkatan Kompetensi dan Pemenuhan Sertifikasi Wajib', [
                        '08.R2.01' => 'Mengikutsertakan pejabat pengelola pada pelatihan dan ujian sertifikasi kompetensi yang dipersyaratkan',
                        '08.R2.02' => 'Menyusun rencana pengembangan kompetensi tahunan dan menyelenggarakan bimbingan teknis internal',
                    ]],
                ],
            ],

            '09' => [
                'nama' => 'Kelemahan Perencanaan dan Pelaporan Kinerja',
                'sub_temuan' => [
                    '09.01' => 'Perencanaan program dan sasaran kegiatan tidak didukung data dasar (baseline) yang akurat',
                    '09.02' => 'Indikator Kinerja Utama (IKU) belum memenuhi kriteria spesifik, terukur, dan relevan (SMART)',
                    '09.03' => 'Target kinerja program/kegiatan utama tidak tercapai sesuai perjanjian kinerja',
                    '09.04' => 'Laporan kinerja (LAKIP/LKj) belum menyajikan analisis capaian dan bukti dukung yang valid',
                    '09.05' => 'Monitoring dan evaluasi berkala atas capaian kinerja program tidak dilaksanakan',
                    '09.06' => 'Output kegiatan telah selesai namun belum memberikan hasil (outcome) atau kemanfaatan nyata',
                ],
                'sebab' => [
                    '09.S1' => ['Kelemahan Perumusan Renstra, Perjanjian Kinerja, dan Indikator Kinerja', [
                        '09.S1.01' => 'Indikator kinerja hanya mengukur penyerapan input/output administratif, bukan outcome program',
                        '09.S1.02' => 'Target kinerja ditetapkan tanpa analisis kapasitas sumber daya dan data baseline tahun sebelumnya',
                        '09.S1.03' => 'Definisi operasional dan formula perhitungan indikator kinerja belum ditetapkan secara baku',
                    ]],
                    '09.S2' => ['Monitoring Capaian Kinerja Triwulanan Tidak Berjalan Efektif', [
                        '09.S2.01' => 'Pengumpulan data capaian kinerja baru dilakukan di akhir tahun saat penyusunan LKj',
                        '09.S2.02' => 'Hambatan pencapaian target pada pertengahan tahun tidak segera dievaluasi dan dimitigasi',
                    ]],
                    '09.S3' => ['Kurangnya Validasi Data Capaian Kinerja dan Perencanaan Pemanfaatan Output', [
                        '09.S3.01' => 'Data realisasi kinerja yang dilaporkan unit teknis tidak diverifikasi kebenaran bukti dukungnya',
                        '09.S3.02' => 'Perencanaan kegiatan tidak mencakup strategi serah terima dan operasionalisasi pemanfaatan output',
                    ]],
                ],
                'akibat' => [
                    '09.A1' => ['Sasaran Strategis Organisasi Tidak Tercapai dan Nilai SAKIP Rendah', [
                        '09.A1.01' => 'Pelaksanaan anggaran tidak diikuti dengan pencapaian sasaran strategis dan manfaat program',
                        '09.A1.02' => 'Nilai evaluasi Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) tidak meningkat',
                    ]],
                    '09.A2' => ['Informasi Laporan Kinerja Tidak Andal sebagai Dasar Evaluasi Pimpinan', [
                        '09.A2.01' => 'Klaim persentase capaian kinerja dalam LKj tidak mencerminkan keberhasilan riil di lapangan',
                        '09.A2.02' => 'Pimpinan tidak memperoleh informasi akurat mengenai kendala kegagalan program',
                    ]],
                    '09.A3' => ['Output Kegiatan Menganggur (Idle) dan Tidak Berdampak bagi Penerima Manfaat', [
                        '09.A3.01' => 'Hasil kajian, aplikasi, atau sarana yang dibangun belum digunakan oleh unit/masyarakat sasaran',
                    ]],
                ],
                'rekomendasi' => [
                    '09.R1' => ['Penyempurnaan Indikator Kinerja (IKU) dan Dokumen Perencanaan', [
                        '09.R1.01' => 'Mereviu dan menyempurnakan IKU serta Perjanjian Kinerja agar berorientasi outcome dan memenuhi kriteria SMART',
                        '09.R1.02' => 'Menetapkan pedoman definisi operasional, sumber data, dan formula perhitungan setiap indikator kinerja',
                    ]],
                    '09.R2' => ['Pelaksanaan Monitoring Kinerja Berkala dan Validasi Data LKj', [
                        '09.R2.01' => 'Melaksanakan evaluasi capaian kinerja setiap triwulan beserta rencana aksi percepatan target yang tertinggal',
                        '09.R2.02' => 'Melakukan verifikasi bukti dukung atas seluruh data capaian kinerja sebelum dituangkan dalam LKj',
                    ]],
                    '09.R3' => ['Optimalisasi Pemanfaatan Output Kegiatan', [
                        '09.R3.01' => 'Menyusun rencana tindak lanjut pemanfaatan output kegiatan agar segera memberikan outcome nyata',
                    ]],
                ],
            ],

            '10' => [
                'nama' => 'Kelemahan Pelayanan dan Pengawasan',
                'sub_temuan' => [
                    '10.01' => 'Standar Pelayanan (SP) dan maklumat pelayanan belum ditetapkan atau belum dipublikasikan',
                    '10.02' => 'Pengawasan dan monitoring lapangan atas penyelenggaraan layanan tidak dilakukan secara berkala',
                    '10.03' => 'Pengaduan masyarakat/pengguna layanan tidak dikelola dan tidak ditindaklanjuti tepat waktu',
                    '10.04' => 'Waktu penyelesaian pelayanan melampaui janji layanan (Service Level Agreement)',
                    '10.05' => 'Rekomendasi hasil pengawasan internal/eksternal sebelumnya belum ditindaklanjuti secara tuntas',
                    '10.06' => 'Sarana, prasarana, dan sistem antrean/informasi pelayanan publik belum memadai',
                ],
                'sebab' => [
                    '10.S1' => ['Belum Ditetapkannya Standar Pelayanan dan Mekanisme Pengelolaan Pengaduan', [
                        '10.S1.01' => 'Unit penyelenggara layanan belum menyusun komponen standar pelayanan sesuai ketentuan pelayanan publik',
                        '10.S1.02' => 'Kanal pengaduan pengguna layanan belum memiliki petugas khusus dan batas waktu penyelesaian',
                        '10.S1.03' => 'Survei Kepuasan Masyarakat (SKM) belum dilaksanakan secara berkala sebagai umpan balik',
                    ]],
                    '10.S2' => ['Lemahnya Supervisi Lapangan dan Pemantauan Tindak Lanjut Hasil Pengawasan', [
                        '10.S2.01' => 'Pimpinan unit jarang melakukan pemantauan langsung terhadap waktu tunggu dan kualitas layanan',
                        '10.S2.02' => 'Tidak adanya pemantauan rutin atas penyelesaian rekomendasi hasil pemeriksaan APIP/BPK',
                    ]],
                    '10.S3' => ['Keterbatasan Dukungan Sarana Layanan dan Integrasi Sistem Pelayanan', [
                        '10.S3.01' => 'Proses permohonan layanan masih dilakukan secara manual dan berbelit-belit',
                        '10.S3.02' => 'Fasilitas ruang layanan dan aksesibilitas bagi pengguna berkebutuhan khusus belum memadai',
                    ]],
                ],
                'akibat' => [
                    '10.A1' => ['Menurunnya Kualitas Pelayanan Publik dan Kepuasan Masyarakat', [
                        '10.A1.01' => 'Pengguna layanan mengalami ketidakpastian persyaratan, biaya, dan waktu penyelesaian layanan',
                        '10.A1.02' => 'Keluhan masyarakat berulang karena pengaduan sebelumnya tidak mendapatkan solusi',
                        '10.A1.03' => 'Indeks Kepuasan Masyarakat (IKM) dan penilaian kepatuhan pelayanan publik rendah',
                    ]],
                    '10.A2' => ['Kelemahan Tata Kelola Berulang Akibat Rekomendasi Pengawasan Tidak Tuntas', [
                        '10.A2.01' => 'Permasalahan yang sama kembali menjadi temuan berulang pada pemeriksaan periode berikutnya',
                        '10.A2.02' => 'Fungsi pengawasan internal tidak memberikan efek perbaikan nyata bagi unit kerja',
                    ]],
                ],
                'rekomendasi' => [
                    '10.R1' => ['Penetapan Standar Pelayanan dan Perbaikan Waktu Layanan (SLA)', [
                        '10.R1.01' => 'Menetapkan dan mempublikasikan Standar Pelayanan secara terbuka pada ruang layanan dan situs web resmi',
                        '10.R1.02' => 'Menyederhanakan alur layanan dan memantau kepatuhan waktu penyelesaian (SLA) secara ketat',
                        '10.R1.03' => 'Melaksanakan Survei Kepuasan Masyarakat (SKM) secara berkala dan mempublikasikan hasilnya',
                    ]],
                    '10.R2' => ['Optimalisasi Pengelolaan Pengaduan dan Pemeliharaan Sarana Layanan', [
                        '10.R2.01' => 'Menunjuk petugas pengelola pengaduan serta menetapkan batas waktu respons dan penyelesaian aduan',
                        '10.R2.02' => 'Melengkapi sarana prasarana pelayanan publik termasuk fasilitas layanan prioritas/disabilitas',
                    ]],
                    '10.R3' => ['Percepatan Penyelesaian Tindak Lanjut Hasil Pengawasan', [
                        '10.R3.01' => 'Melaksanakan rapat pemantauan bulanan untuk menuntaskan seluruh rekomendasi hasil pemeriksaan yang belum selesai',
                        '10.R3.02' => 'Meningkatkan frekuensi supervisi dan monitoring berkala oleh pimpinan atas unit layanan operasional',
                    ]],
                ],
            ],
        ];
    }

    /**
     * 15 Kode Induk Tinjut beserta Sub Kode Tinjut (tidak terikat dengan Kode Temuan).
     *
     * @return array<string, array{0: string, 1: array<string, string>}>
     */
    private function tinjutData(): array
    {
        return [
            '01' => ['Penyetoran Uang ke Kas Negara/Daerah', [
                '01.01' => 'Bukti setor pengembalian kerugian negara (SSPB/SSBP/NTPN) telah diverifikasi lunas',
                '01.02' => 'Bukti setor pajak (PPN/PPh) dan denda keterlambatan telah diterima di Kas Negara',
                '01.03' => 'Penyetoran sisa UP/TUP dan penerimaan PNBP telah tercatat pada rekening Kas Negara',
                '01.04' => 'Angsuran tuntutan ganti rugi (TGR) telah disetorkan sesuai jadwal SKTJM',
            ]],
            '02' => ['Pemotongan atau Perhitungan Pembayaran Termin', [
                '02.01' => 'Berita acara pemotongan pembayaran termin terakhir atas kekurangan volume pekerjaan',
                '02.02' => 'Bukti pemotongan tagihan penyedia atas kelebihan pembayaran harga satuan',
                '02.03' => 'Kompensasi nilai denda keterlambatan pada SPM pembayaran akhir',
            ]],
            '03' => ['Pencairan Jaminan Bank / Garansi', [
                '03.01' => 'Bukti pencairan jaminan pelaksanaan atas pemutusan kontrak wanprestasi',
                '03.02' => 'Bukti pencairan jaminan uang muka dan penyetoran ke Kas Negara',
                '03.03' => 'Bukti klaim jaminan pemeliharaan atas cacat mutu pekerjaan',
            ]],
            '04' => ['Perbaikan atau Penggantian Fisik Pekerjaan/Barang', [
                '04.01' => 'Berita Acara Pemeriksaan Fisik penyelesaian kekurangan volume pekerjaan konstruksi',
                '04.02' => 'Berita Acara penggantian barang pengadaan sesuai spesifikasi teknis kontrak',
                '04.03' => 'Dokumentasi dan hasil uji mutu perbaikan pekerjaan selama masa pemeliharaan',
            ]],
            '05' => ['Penarikan, Pengamanan, dan Sertifikasi Aset (BMN)', [
                '05.01' => 'Berita Acara penarikan kembali kendaraan/aset negara dari pihak yang tidak berhak',
                '05.02' => 'Pengurusan sertifikat kepemilikan tanah/bangunan dan pengamanan fisik BMN',
                '05.03' => 'Pencatatan dan pemutakhiran daftar inventaris barang pada aplikasi SIMAK-BMN/SAKTI',
                '05.04' => 'Persetujuan pemanfaatan/sewa atau penghapusan BMN dari Pengelola Barang',
            ]],
            '06' => ['Penyusunan dan Penetapan SOP / Kebijakan Internal', [
                '06.01' => 'Penetapan Standar Operasional Prosedur (SOP) baru oleh pimpinan satuan kerja',
                '06.02' => 'Revisi petunjuk teknis atau pedoman kerja selaras dengan regulasi terbaru',
                '06.03' => 'Penetapan Standar Pelayanan (SP) dan maklumat pelayanan publik',
            ]],
            '07' => ['Penerbitan Teguran Tertulis dan Sanksi Administratif', [
                '07.01' => 'Surat teguran tertulis kepada PPK, Pokja Pemilihan, atau Pejabat Pengadaan',
                '07.02' => 'Surat peringatan/teguran tertulis kepada Bendahara atau pelaksana kegiatan',
                '07.03' => 'Surat peringatan dan penetapan daftar hitam (Blacklist) kepada penyedia barang/jasa',
            ]],
            '08' => ['Penjatuhan Hukuman Disiplin Pegawai', [
                '08.01' => 'Surat Keputusan penjatuhan hukuman disiplin ringan kepada pegawai terkait',
                '08.02' => 'Surat Keputusan penjatuhan hukuman disiplin sedang sesuai PP Disiplin PNS',
                '08.03' => 'Surat Keputusan penjatuhan hukuman disiplin berat atau pemberhentian dari jabatan',
            ]],
            '09' => ['Kelengkapan Dokumen Pertanggungjawaban dan Kearsipan', [
                '09.01' => 'Penyampaian kelengkapan bukti pertanggungjawaban belanja dan perjalanan dinas',
                '09.02' => 'Penertiban register penomoran surat, SK, kontrak, dan berita acara serah terima',
                '09.03' => 'Penataan arsip terpusat dan digitalisasi dokumen kontrak/keuangan',
            ]],
            '10' => ['Koreksi Akuntansi, Pembukuan, dan Revisi Anggaran', [
                '10.01' => 'Bukti ralat SPM/SP2D dan jurnal koreksi kesalahan akun belanja pada aplikasi SAKTI',
                '10.02' => 'Pengesahan revisi DIPA/POK dan pemutakhiran Rencana Penarikan Dana (Halaman III DIPA)',
                '10.03' => 'Berita Acara Rekonsiliasi (BAR) kas, pajak, PNBP, dan aset dengan KPPN/KPKNL',
            ]],
            '11' => ['Penataan Organisasi, Tupoksi, dan Pendelegasian Wewenang', [
                '11.01' => 'Surat Keputusan penetapan uraian tugas, pemisahan fungsi, dan pendelegasian wewenang',
                '11.02' => 'Pembentukan atau revitalisasi Satgas SPIP dan tim kerja beserta rencana kerjanya',
                '11.03' => 'Notula dan bukti pelaksanaan rapat koordinasi berkala lintas unit kerja',
            ]],
            '12' => ['Pembinaan, Pelatihan, dan Sertifikasi SDM', [
                '12.01' => 'Sertifikat kelulusan ujian kompetensi Pengadaan Barang/Jasa atau Bendahara Negara (BNT)',
                '12.02' => 'Laporan pelaksanaan bimbingan teknis, sosialisasi regulasi, atau diklat pegawai',
                '12.03' => 'Pemutakhiran dokumen Anjab-ABK dan penataan distribusi penempatan pegawai',
            ]],
            '13' => ['Perbaikan Perencanaan, IKU, dan Laporan Kinerja', [
                '13.01' => 'Dokumen reviu Indikator Kinerja Utama (IKU) dan Perjanjian Kinerja berorientasi outcome',
                '13.02' => 'Laporan monitoring evaluasi kinerja triwulanan dan validasi bukti dukung LKj/LAKIP',
                '13.03' => 'Berita acara serah terima operasionalisasi dan pemanfaatan output kegiatan',
            ]],
            '14' => ['Pengembangan dan Integrasi Sistem Informasi', [
                '14.01' => 'Implementasi atau pemutakhiran modul aplikasi pelayanan dan pengendalian internal',
                '14.02' => 'Penertiban otorisasi hak akses pengguna (user access matrix) pada sistem informasi',
                '14.03' => 'Pemutakhiran dan validasi akurasi basis data operasional satuan kerja',
            ]],
            '15' => ['Pelimpahan Proses Hukum / Penyelesaian Khusus', [
                '15.01' => 'Penerbitan SK Pembebanan Penggantian Kerugian Negara melalui Majelis TP-TGR',
                '15.02' => 'Penyerahan pengurusan piutang negara macet kepada PUPN/KPKNL',
                '15.03' => 'Pelimpahan berkas pemeriksaan berindikasi tindak pidana kepada Aparat Penegak Hukum (APH)',
            ]],
        ];
    }
}
