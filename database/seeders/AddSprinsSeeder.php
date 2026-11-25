<?php

namespace Database\Seeders;

use App\Models\Sprin;
use App\Models\SprinReview;
use App\Models\Tim;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AddSprinsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFilePerintah = 'sprin/surat-perintah/JoAJcvB6976jC69n1mGFprY6yz0eLHOxoz8OxPMY.pdf';
        $defaultNamePerintah = 'Surat Perintah Tugas Pemeriksaan.pdf';
        $defaultFilePemberitahuan = 'sprin/surat-pemberitahuan/tEknGnvBSFjidLrs0ukm4hiuPdDlcil6ih2sCn8H.pdf';
        $defaultNamePemberitahuan = 'Surat Pemberitahuan Audit.pdf';

        $sprinConfigs = [
            [
                'tim_id' => 4, // TEAM-PICK/100.29591 (Jawa Barat)
                'no_sprin' => 'SPRIN-004/PW-JB/2026',
                'no_sp' => 'SP-004/PW-JB/2026',
                'tahap' => '1',
                'start' => '2026-11-20',
                'review_status' => SprinReview::STATUS_DISETUJUI,
                'catatan' => 'Sprin disetujui, koordinasi dengan kepala desa setempat sebelum pelaksanaan.',
                'keterangan' => 'Audit kepatuhan pengadaan barang dan operasional desa.',
            ],
            [
                'tim_id' => 5, // Tim-Example/2026 (WIL IV)
                'no_sprin' => 'SPRIN-005/PN-WIL4/2026',
                'no_sp' => 'SP-005/PN-WIL4/2026',
                'tahap' => '1',
                'start' => '2026-11-22',
                'review_status' => SprinReview::STATUS_KOREKSI,
                'catatan' => 'Mohon lampirkan rincian jadwal kunjungan lapangan per minggu.',
                'keterangan' => 'Audit progres belanja modal wilayah timur.',
            ],
            [
                'tim_id' => 6, // TIM WIL I Diponegoro (WIL I)
                'no_sprin' => 'SPRIN-006/PN-WIL1/2026',
                'no_sp' => 'SP-006/PN-WIL1/2026',
                'tahap' => '1',
                'start' => '2026-11-25',
                'review_status' => SprinReview::STATUS_DISETUJUI,
                'catatan' => 'Sesuai dengan ketentuan rencana audit tahunan 2026.',
                'keterangan' => 'Pemeriksaan kepatuhan anggaran tahap pertama.',
            ],
            [
                'tim_id' => 7, // TIM WIL III Hasanuddin (WIL III)
                'no_sprin' => 'SPRIN-007/PN-WIL3/2026',
                'no_sp' => 'SP-007/PN-WIL3/2026',
                'tahap' => '1',
                'start' => '2026-11-26',
                'review_status' => null, // Belum direviu (tombol Reviu merah)
                'catatan' => null,
                'keterangan' => 'Pemeriksaan operasional dan logistik terpadu.',
            ],
            [
                'tim_id' => 8, // TIM Wilayah DKI Jakarta (31)
                'no_sprin' => 'SPRIN-008/PW-DKI/2026',
                'no_sp' => 'SP-008/PW-DKI/2026',
                'tahap' => '1',
                'start' => '2026-11-27',
                'review_status' => SprinReview::STATUS_DISETUJUI,
                'catatan' => 'Dokumen lengkap dan tim sudah siap terjun ke lapangan.',
                'keterangan' => 'Pemeriksaan belanja hibah dan bantuan sosial DKI Jakarta.',
            ],
            [
                'tim_id' => 9, // TIM Wilayah Jawa Tengah (33)
                'no_sprin' => 'SPRIN-009/PW-JT/2026',
                'no_sp' => 'SP-009/PW-JT/2026',
                'tahap' => '1',
                'start' => '2026-11-28',
                'review_status' => SprinReview::STATUS_KOREKSI,
                'catatan' => 'Perbaiki tanggal akhir penugasan agar genap 14 hari kerja.',
                'keterangan' => 'Audit kepatuhan tata kelola anggaran dan aset Jawa Tengah.',
            ],
            [
                'tim_id' => 10, // TIM Wilayah DI Yogyakarta (34)
                'no_sprin' => 'SPRIN-010/PW-DIY/2026',
                'no_sp' => 'SP-010/PW-DIY/2026',
                'tahap' => '1',
                'start' => '2026-11-29',
                'review_status' => null, // Belum direviu (tombol Reviu merah)
                'catatan' => null,
                'keterangan' => 'Pemeriksaan program keistimewaan dan belanja modal DIY.',
            ],
            [
                'tim_id' => 11, // TIM Wilayah Jawa Timur (35)
                'no_sprin' => 'SPRIN-011/PW-JTM/2026',
                'no_sp' => 'SP-011/PW-JTM/2026',
                'tahap' => '1',
                'start' => '2026-12-01',
                'review_status' => SprinReview::STATUS_DISETUJUI,
                'catatan' => 'Disetujui. Lakukan kickoff meeting secara daring terlebih dahulu.',
                'keterangan' => 'Pemeriksaan belanja infrastruktur wilayah Jawa Timur.',
            ],
            [
                'tim_id' => 12, // TIM Wilayah Banten (36)
                'no_sprin' => 'SPRIN-012/PW-BTN/2026',
                'no_sp' => 'SP-012/PW-BTN/2026',
                'tahap' => '1',
                'start' => '2026-12-02',
                'review_status' => null, // Belum direviu (tombol Reviu merah)
                'catatan' => null,
                'keterangan' => 'Pemeriksaan operasional dinas pendapatan dan perizinan.',
            ],
            [
                'tim_id' => 6, // TIM WIL I Diponegoro (WIL I) - Tahap 2
                'no_sprin' => 'SPRIN-013/PN-WIL1-T2/2026',
                'no_sp' => 'SP-013/PN-WIL1-T2/2026',
                'tahap' => '2',
                'start' => '2026-12-10',
                'review_status' => null, // Belum direviu (tombol Reviu merah)
                'catatan' => null,
                'keterangan' => 'Lanjutan pemeriksaan tindak lanjut rekomendasi tahap 1.',
            ],
        ];

        foreach ($sprinConfigs as $cfg) {
            $tim = Tim::query()->findOrFail($cfg['tim_id']);

            $startDate = Carbon::parse($cfg['start']);
            $endDate = (clone $startDate)->addDays(14);

            $sprin = Sprin::query()->updateOrCreate(
                ['no_sprin' => $cfg['no_sprin']],
                [
                    'user_id' => 1,
                    'no_surat_pemberitahuan' => $cfg['no_sp'],
                    'file_surat_perintah_id' => $defaultFilePerintah,
                    'file_surat_perintah_name' => $defaultNamePerintah,
                    'file_surat_pemberitahuan_id' => $defaultFilePemberitahuan,
                    'file_surat_pemberitahuan_name' => $defaultNamePemberitahuan,
                    'tgl_mulai' => $startDate->format('Y-m-d'),
                    'tgl_akhir' => $endDate->format('Y-m-d'),
                    'plotting_id' => $tim->lingkup === 'Nasional' ? $tim->plotting_id : null,
                    'kode_wilayah' => $tim->lingkup === 'Wilayah' ? $tim->province_code : null,
                    'tim_id' => $tim->id,
                    'katim' => $tim->ketua_tim_id,
                    'katim_2' => $tim->ketua_tim_2_id,
                    'dalnis' => $tim->dalnis_id,
                    'daltu' => $tim->daltu_id,
                    'tahap' => $cfg['tahap'],
                    'sprin_tipe' => $tim->lingkup,
                    'keterangan' => $cfg['keterangan'],
                    'approved_by_daltu_date' => $cfg['review_status'] === SprinReview::STATUS_DISETUJUI ? now() : null,
                    'created_by' => 1,
                ]
            );

            if ($cfg['review_status']) {
                $reviewerId = $tim->daltu_id ?: 1;
                $sprin->reviews()->create([
                    'user_id' => $reviewerId,
                    'status' => $cfg['review_status'],
                    'catatan' => $cfg['catatan'],
                ]);
            }
        }
    }
}
