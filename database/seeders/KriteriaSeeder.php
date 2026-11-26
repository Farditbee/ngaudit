<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use Illuminate\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    /**
     * Seed data kriteria pemeriksaan beserta pemetaan bidang (is_opsnal, is_logistik, is_sdm, is_garkeu).
     */
    public function run(): void
    {
        foreach ($this->data() as $item) {
            Kriteria::query()->updateOrCreate(
                ['nama' => $item['nama']],
                [
                    'is_opsnal' => $item['is_opsnal'],
                    'is_logistik' => $item['is_logistik'],
                    'is_sdm' => $item['is_sdm'],
                    'is_garkeu' => $item['is_garkeu'],
                ],
            );
        }
    }

    /**
     * @return list<array{nama: string, is_opsnal: bool, is_logistik: bool, is_sdm: bool, is_garkeu: bool}>
     */
    private function data(): array
    {
        return [
            // Bidang Anggaran & Keuangan (Garkeu)
            [
                'nama' => 'Undang-Undang Nomor 17 Tahun 2003 tentang Keuangan Negara',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Undang-Undang Nomor 1 Tahun 2004 tentang Perbendaharaan Negara',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Pemerintah Nomor 45 Tahun 2013 jo. PP Nomor 50 Tahun 2018 tentang Tata Cara Pelaksanaan Anggaran Pendapatan dan Belanja Negara',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan Nomor 210/PMK.05/2022 tentang Tata Cara Pembayaran dalam Rangka Pelaksanaan Anggaran Pendapatan dan Belanja Negara',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan Nomor 62 Tahun 2023 tentang Perencanaan Anggaran, Pelaksanaan Anggaran, serta Akuntansi dan Pelaporan Keuangan',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan tentang Standar Biaya Masukan (SBM) Tahun Anggaran Berjalan',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan Nomor 113/PMK.05/2012 tentang Perjalanan Dinas Dalam Negeri bagi Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Undang-Undang Nomor 9 Tahun 2018 tentang Penerimaan Negara Bukan Pajak (PNBP)',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],

            // Bidang Logistik / Pengadaan & BMN
            [
                'nama' => 'Peraturan Presiden Nomor 16 Tahun 2018 jo. Perpres Nomor 12 Tahun 2021 tentang Pengadaan Barang/Jasa Pemerintah',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Pemerintah Nomor 27 Tahun 2014 jo. PP Nomor 28 Tahun 2020 tentang Pengelolaan Barang Milik Negara/Daerah',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan Nomor 181/PMK.06/2016 tentang Penatausahaan Barang Milik Negara',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Menteri Keuangan Nomor 111/PMK.06/2016 tentang Tata Cara Pelaksanaan Pemindahtanganan Barang Milik Negara',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan LKPP Nomor 12 Tahun 2021 tentang Pedoman Pelaksanaan Pengadaan Barang/Jasa Pemerintah Melalui Penyedia',
                'is_opsnal' => false,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Pedoman Standarisasi Sarana, Prasarana, Pemeliharaan Peralatan, dan Pergudangan Logistik',
                'is_opsnal' => true,
                'is_logistik' => true,
                'is_sdm' => false,
                'is_garkeu' => false,
            ],

            // Bidang Sumber Daya Manusia (SDM)
            [
                'nama' => 'Undang-Undang Nomor 20 Tahun 2023 tentang Aparatur Sipil Negara',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan Pemerintah Nomor 11 Tahun 2017 jo. PP Nomor 17 Tahun 2020 tentang Manajemen Pegawai Negeri Sipil',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan Pemerintah Nomor 94 Tahun 2021 tentang Disiplin Pegawai Negeri Sipil',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan Menteri PANRB Nomor 1 Tahun 2020 tentang Pedoman Analisis Jabatan dan Analisis Beban Kerja',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Peraturan Pemerintah Nomor 30 Tahun 2019 tentang Penilaian Kinerja Pegawai Negeri Sipil',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Ketentuan Pembayaran Tunjangan Kinerja, Uang Makan, dan Pembinaan Karier Personel',
                'is_opsnal' => false,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => true,
            ],

            // Bidang Operasional (Opsnal) & Lintas Bidang
            [
                'nama' => 'Peraturan Pemerintah Nomor 60 Tahun 2008 tentang Sistem Pengendalian Intern Pemerintah (SPIP)',
                'is_opsnal' => true,
                'is_logistik' => true,
                'is_sdm' => true,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Peraturan Presiden Nomor 29 Tahun 2014 tentang Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => true,
            ],
            [
                'nama' => 'Undang-Undang Nomor 25 Tahun 2009 tentang Pelayanan Publik',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Standar Operasional Prosedur (SOP) Pelaksanaan Tugas Operasional, Patroli, dan Pelayanan Satuan Kerja',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => false,
                'is_garkeu' => false,
            ],
            [
                'nama' => 'Pedoman Tata Naskah Dinas, Kearsipan, dan Pelaporan Operasional Berkala',
                'is_opsnal' => true,
                'is_logistik' => false,
                'is_sdm' => true,
                'is_garkeu' => false,
            ],
        ];
    }
}
