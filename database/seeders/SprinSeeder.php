<?php

namespace Database\Seeders;

use App\Models\Sprin;
use App\Models\SprinReview;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SprinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tims = Tim::query()
            ->where('lingkup', 'Dinas')
            ->orderBy('id')
            ->get();

        if ($tims->isEmpty()) {
            return;
        }

        $sprinDefinitions = [
            [
                'tim_index' => 0,
                'no_sprin' => 'SPRIN-001/ITDA-LT/I/2026',
                'no_surat_pemberitahuan' => 'SP-001/ITDA-LT/I/2026',
                'tahap' => '1',
                'tgl_mulai' => '2026-01-10',
                'tgl_akhir' => '2026-01-24',
                'approved' => true,
                'keterangan' => 'Audit Kinerja dan Kepatuhan atas Pengelolaan Dana BOK dan Operasional Puskesmas Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 1,
                'no_sprin' => 'SPRIN-002/ITDA-LT/I/2026',
                'no_surat_pemberitahuan' => 'SP-002/ITDA-LT/I/2026',
                'tahap' => '1',
                'tgl_mulai' => '2026-01-15',
                'tgl_akhir' => '2026-01-29',
                'approved' => true,
                'keterangan' => 'Audit Pengelolaan Dana Bantuan Operasional Sekolah (BOS) dan Pengadaan Sarana Prasarana Sekolah Dasar Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 2,
                'no_sprin' => 'SPRIN-003/ITDA-LT/II/2026',
                'no_surat_pemberitahuan' => 'SP-003/ITDA-LT/II/2026',
                'tahap' => '2',
                'tgl_mulai' => '2026-02-01',
                'tgl_akhir' => '2026-02-15',
                'approved' => true,
                'keterangan' => 'Audit Kepatuhan Pelaksanaan Proyek Pembangunan dan Pemeliharaan Jalan Kabupaten Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 3,
                'no_sprin' => 'SPRIN-004/ITDA-LT/II/2026',
                'no_surat_pemberitahuan' => 'SP-004/ITDA-LT/II/2026',
                'tahap' => '2',
                'tgl_mulai' => '2026-02-10',
                'tgl_akhir' => '2026-02-24',
                'approved' => false,
                'keterangan' => 'Audit Operasional Pemeliharaan Jaringan Irigasi dan Pengendalian Banjir Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 4,
                'no_sprin' => 'SPRIN-005/ITDA-LT/KH/2026',
                'no_surat_pemberitahuan' => 'SP-005/ITDA-LT/KH/2026',
                'tahap' => 'Khusus',
                'tgl_mulai' => '2026-02-15',
                'tgl_akhir' => '2026-03-01',
                'approved' => true,
                'keterangan' => 'Pemeriksaan Khusus (Audit Investigatif) atas Pengelolaan Retribusi Sampah dan Operasional TPA Tahun Anggaran 2025.',
            ],
        ];

        foreach ($sprinDefinitions as $def) {
            $tim = $tims->get($def['tim_index']) ?? $tims->first();
            $approvedDate = $def['approved']
                ? Carbon::parse($def['tgl_mulai'])->subDays(2)->setTime(10, 0)
                : null;

            $sprin = Sprin::query()->updateOrCreate(
                ['no_sprin' => $def['no_sprin']],
                [
                    'user_id' => 1,
                    'no_surat_pemberitahuan' => $def['no_surat_pemberitahuan'],
                    'tgl_mulai' => $def['tgl_mulai'],
                    'tgl_akhir' => $def['tgl_akhir'],
                    'struktur_perangkat_daerah_id' => $tim->struktur_perangkat_daerah_id,
                    'plotting_id' => $tim->struktur_perangkat_daerah_id,
                    'kode_wilayah' => null,
                    'tim_id' => $tim->id,
                    'katim' => $tim->ketua_tim_id,
                    'katim_2' => $tim->ketua_tim_2_id,
                    'dalnis' => $tim->dalnis_id,
                    'daltu' => $tim->daltu_id,
                    'tahap' => $def['tahap'],
                    'sprin_tipe' => 'Dinas',
                    'keterangan' => $def['keterangan'],
                    'approved_by_daltu_date' => $approvedDate,
                    'created_by' => 1,
                ]
            );

            if ($def['approved']) {
                SprinReview::query()->updateOrCreate(
                    [
                        'sprin_id' => $sprin->id,
                        'user_id' => $tim->daltu_id,
                    ],
                    [
                        'status' => SprinReview::STATUS_DISETUJUI,
                        'catatan' => 'Surat perintah tugas telah diverifikasi dan disetujui untuk dilaksanakan.',
                        'created_at' => $approvedDate,
                        'updated_at' => $approvedDate,
                    ]
                );
            }
        }

        // Seeder Sprin Khusus Lingkup Kecamatan
        $timsKec = Tim::query()
            ->where('lingkup', 'Kecamatan')
            ->orderBy('id')
            ->get();

        if ($timsKec->isEmpty()) {
            return;
        }

        $sprinKecDefinitions = [
            [
                'tim_index' => 0,
                'no_sprin' => 'SPRIN-001/ITDA-LT/KEC-GS/I/2026',
                'no_surat_pemberitahuan' => 'SP-001/ITDA-LT/KEC-GS/I/2026',
                'tahap' => '1',
                'tgl_mulai' => '2026-01-12',
                'tgl_akhir' => '2026-01-26',
                'approved' => true,
                'keterangan' => 'Audit Kinerja dan Kepatuhan atas Pengelolaan Alokasi Dana Kampung (ADK) dan Dana Desa (DD) se-Kecamatan Gunung Sugih Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 1,
                'no_sprin' => 'SPRIN-002/ITDA-LT/KEC-TB/I/2026',
                'no_surat_pemberitahuan' => 'SP-002/ITDA-LT/KEC-TB/I/2026',
                'tahap' => '1',
                'tgl_mulai' => '2026-01-18',
                'tgl_akhir' => '2026-02-01',
                'approved' => true,
                'keterangan' => 'Audit Kinerja Pelayanan Administrasi Terpadu Kecamatan (PATEN) dan Penatausahaan Barang Milik Daerah di Kantor Kecamatan Terbanggi Besar Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 2,
                'no_sprin' => 'SPRIN-003/ITDA-LT/KEC-SA/II/2026',
                'no_surat_pemberitahuan' => 'SP-003/ITDA-LT/KEC-SA/II/2026',
                'tahap' => '2',
                'tgl_mulai' => '2026-02-05',
                'tgl_akhir' => '2026-02-19',
                'approved' => true,
                'keterangan' => 'Audit Kepatuhan Penatausahaan dan Pemungutan Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) serta Retribusi Daerah Kecamatan Seputih Agung Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 3,
                'no_sprin' => 'SPRIN-004/ITDA-LT/KEC-BRN/II/2026',
                'no_surat_pemberitahuan' => 'SP-004/ITDA-LT/KEC-BRN/II/2026',
                'tahap' => '2',
                'tgl_mulai' => '2026-02-12',
                'tgl_akhir' => '2026-02-26',
                'approved' => false,
                'keterangan' => 'Audit Operasional Pelaksanaan Program Pemberdayaan Masyarakat dan Bantuan Sosial pada Kecamatan Bumi Ratu Nuban Tahun Anggaran 2025.',
            ],
            [
                'tim_index' => 4,
                'no_sprin' => 'SPRIN-005/ITDA-LT/KEC-KL/KH/2026',
                'no_surat_pemberitahuan' => 'SP-005/ITDA-LT/KEC-KL/KH/2026',
                'tahap' => 'Khusus',
                'tgl_mulai' => '2026-02-20',
                'tgl_akhir' => '2026-03-06',
                'approved' => true,
                'keterangan' => 'Pemeriksaan Khusus (Audit Investigatif) atas Indikasi Penyimpangan Pengelolaan Anggaran Operasional dan Retribusi Izin Usaha Kecamatan Kalirejo Tahun Anggaran 2025.',
            ],
        ];

        foreach ($sprinKecDefinitions as $kDef) {
            $tim = $timsKec->get($kDef['tim_index']) ?? $timsKec->first();
            $approvedDate = $kDef['approved']
                ? Carbon::parse($kDef['tgl_mulai'])->subDays(2)->setTime(10, 0)
                : null;

            $sprin = Sprin::query()->updateOrCreate(
                ['no_sprin' => $kDef['no_sprin']],
                [
                    'user_id' => 1,
                    'no_surat_pemberitahuan' => $kDef['no_surat_pemberitahuan'],
                    'tgl_mulai' => $kDef['tgl_mulai'],
                    'tgl_akhir' => $kDef['tgl_akhir'],
                    'struktur_perangkat_daerah_id' => $tim->struktur_perangkat_daerah_id,
                    'plotting_id' => $tim->struktur_perangkat_daerah_id,
                    'kode_wilayah' => null,
                    'tim_id' => $tim->id,
                    'katim' => $tim->ketua_tim_id,
                    'katim_2' => $tim->ketua_tim_2_id,
                    'dalnis' => $tim->dalnis_id,
                    'daltu' => $tim->daltu_id,
                    'tahap' => $kDef['tahap'],
                    'sprin_tipe' => 'Kecamatan',
                    'keterangan' => $kDef['keterangan'],
                    'approved_by_daltu_date' => $approvedDate,
                    'created_by' => 1,
                ]
            );

            if ($kDef['approved']) {
                SprinReview::query()->updateOrCreate(
                    [
                        'sprin_id' => $sprin->id,
                        'user_id' => $tim->daltu_id,
                    ],
                    [
                        'status' => SprinReview::STATUS_DISETUJUI,
                        'catatan' => 'Surat perintah tugas tingkat Kecamatan telah diverifikasi dan disetujui untuk dilaksanakan.',
                        'created_at' => $approvedDate,
                        'updated_at' => $approvedDate,
                    ]
                );
            }
        }
    }
}
