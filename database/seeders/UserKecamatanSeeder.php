<?php

namespace Database\Seeders;

use App\Models\StrukturPerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserKecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123');

        // 1. Pastikan Admin Kecamatan ada
        User::query()->updateOrCreate(
            ['username' => 'adminkecamatan'],
            [
                'name' => 'Admin Kecamatan',
                'email' => 'admin.kecamatan@eaudit.test',
                'phone' => '081299990001',
                'role' => 'Admin Kecamatan',
                'password' => $defaultPassword,
                'level' => 'Kecamatan',
                'struktur_perangkat_daerah_id' => null,
                'kode_wilayah' => null,
                'bidang' => null,
                'jabatan' => 'Administrator Wilayah Kecamatan',
                'created_by' => 1,
            ]
        );

        $kecamatanList = StrukturPerangkatDaerah::kecamatan()->get();

        // 2. 7 Akun Auditee Tambahan pada Sub-Unit di Bawah Kecamatan Utama
        $subUnitSpecs = [
            [
                'kecamatan_keyword' => 'Gunung Sugih',
                'seksi_keyword' => 'Tata Pemerintahan',
                'username' => 'auditee_tapem_gs',
                'name' => 'Auditee Seksi Tapem Kec. Gunung Sugih',
                'email' => 'auditee.tapemgs@eaudit.test',
                'phone' => '081600000011',
                'bidang' => 'Operasional',
                'jabatan' => 'Kasi Tata Pemerintahan Kec. Gunung Sugih',
            ],
            [
                'kecamatan_keyword' => 'Terbanggi Besar',
                'seksi_keyword' => 'Ketentraman',
                'username' => 'auditee_trantib_tb',
                'name' => 'Auditee Seksi Trantib Kec. Terbanggi Besar',
                'email' => 'auditee.trantibtb@eaudit.test',
                'phone' => '081600000012',
                'bidang' => 'Operasional',
                'jabatan' => 'Kasi Trantib Kec. Terbanggi Besar',
            ],
            [
                'kecamatan_keyword' => 'Seputih Agung',
                'seksi_keyword' => 'Pembangunan',
                'username' => 'auditee_pmd_sa',
                'name' => 'Auditee Seksi PMD Kec. Seputih Agung',
                'email' => 'auditee.pmdsa@eaudit.test',
                'phone' => '081600000013',
                'bidang' => 'Manajemen Anggaran dan Keuangan',
                'jabatan' => 'Kasi Pemberdayaan Masyarakat Kec. Seputih Agung',
            ],
            [
                'kecamatan_keyword' => 'Bumi Ratu Nuban',
                'seksi_keyword' => 'Kesejahteraan',
                'username' => 'auditee_kesra_brn',
                'name' => 'Auditee Seksi Kesra Kec. Bumi Ratu Nuban',
                'email' => 'auditee.kesrabrn@eaudit.test',
                'phone' => '081600000014',
                'bidang' => 'Sumber Daya Manusia',
                'jabatan' => 'Kasi Kesejahteraan Rakyat Kec. Bumi Ratu Nuban',
            ],
            [
                'kecamatan_keyword' => 'Bekri',
                'seksi_keyword' => 'Ketentraman',
                'username' => 'auditee_trantib_bekri',
                'name' => 'Auditee Seksi Trantib Kec. Bekri',
                'email' => 'auditee.trantibbekri@eaudit.test',
                'phone' => '081600000015',
                'bidang' => 'Operasional',
                'jabatan' => 'Kasi Trantib Kec. Bekri',
            ],
            [
                'kecamatan_keyword' => 'Bangun Rejo',
                'seksi_keyword' => 'Tata Pemerintahan',
                'username' => 'auditee_tapem_br',
                'name' => 'Auditee Seksi Tapem Kec. Bangun Rejo',
                'email' => 'auditee.tapembr@eaudit.test',
                'phone' => '081600000016',
                'bidang' => 'Logistik',
                'jabatan' => 'Kasi Tata Pemerintahan Kec. Bangun Rejo',
            ],
            [
                'kecamatan_keyword' => 'Kalirejo',
                'seksi_keyword' => 'Pengembangan Potensi',
                'username' => 'auditee_pendapatan_kalirejo',
                'name' => 'Auditee Seksi Pendapatan Kec. Kalirejo',
                'email' => 'auditee.pendapatankalirejo@eaudit.test',
                'phone' => '081600000017',
                'bidang' => 'Manajemen Anggaran dan Keuangan',
                'jabatan' => 'Kasi Pendapatan Kec. Kalirejo',
            ],
        ];

        foreach ($subUnitSpecs as $spec) {
            $kec = StrukturPerangkatDaerah::query()
                ->where('level', 1)
                ->where('jenis_unit', 'Kecamatan')
                ->where('nama_unit', 'like', "%{$spec['kecamatan_keyword']}%")
                ->first();

            $subUnit = $kec
                ? StrukturPerangkatDaerah::query()
                    ->where('parent_id', $kec->id)
                    ->where('nama_unit', 'like', "%{$spec['seksi_keyword']}%")
                    ->first()
                : null;

            User::query()->updateOrCreate(
                ['username' => $spec['username']],
                [
                    'name' => $spec['name'],
                    'email' => $spec['email'],
                    'phone' => $spec['phone'],
                    'role' => 'Auditee',
                    'password' => $defaultPassword,
                    'level' => 'Kecamatan',
                    'struktur_perangkat_daerah_id' => $subUnit?->id ?? $kec?->id,
                    'kode_wilayah' => $subUnit?->kode_wilayah ?? $subUnit?->kode_unit ?? null,
                    'bidang' => $spec['bidang'],
                    'jabatan' => $spec['jabatan'],
                    'created_by' => 1,
                ]
            );
        }

        // 3. 17 Auditor Tingkat Kecamatan
        $auditorKecNames = [
            'Dian Prasetya',
            'Maya Safitri',
            'Rudi Hartono',
            'Tri Wahyudi',
            'Linda Kusuma',
            'Agus Santoso',
            'Ratna Juwita',
            'Hendri Gunawan',
            'Sri Mulyani',
            'Wahyu Pratama',
            'Rina Astuti',
            'Dwi Cahyono',
            'Nur Hidayat',
            'Angga Wijaya',
            'Fitri Lestari',
            'Bayu Aji',
            'Siti Rahayu',
        ];

        foreach ($auditorKecNames as $idx => $name) {
            $num = $idx + 1;
            $randomKec = $kecamatanList->isNotEmpty()
                ? $kecamatanList[$idx % $kecamatanList->count()]
                : null;

            User::query()->updateOrCreate(
                ['username' => "auditor_kec_{$num}"],
                [
                    'name' => $name,
                    'email' => "auditor.kec{$num}@eaudit.test",
                    'phone' => sprintf('08221000%04d', $num),
                    'role' => 'Auditor',
                    'password' => $defaultPassword,
                    'level' => 'Kecamatan',
                    'struktur_perangkat_daerah_id' => $randomKec?->id,
                    'kode_wilayah' => $randomKec?->kode_wilayah ?? null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Auditor Kecamatan',
                    'created_by' => 1,
                ]
            );
        }

        // 4. Koordinator dan Pengendali (Daltu) Kecamatan (6 Orang)
        $daltuKecNames = [
            'Dr. H. Ahmad Marzuki, M.M.',
            'Dra. Hj. Nurul Hidayah, M.Si.',
            'Ir. Sugeng Priyono, M.T.',
            'Drs. M. Yusuf Ibrahim, M.Si.',
            'Dr. Endang Sulastri, S.E., M.Akt.',
            'H. Bambang Irawan, S.H., M.H.',
        ];

        foreach ($daltuKecNames as $idx => $name) {
            $num = $idx + 1;
            $randomKec = $kecamatanList->isNotEmpty()
                ? $kecamatanList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "daltu_kec_{$num}"],
                [
                    'name' => $name,
                    'email' => "daltu.kec{$num}@eaudit.test",
                    'phone' => sprintf('08231000%04d', $num),
                    'role' => 'Koordinator dan Pengendali',
                    'password' => $defaultPassword,
                    'level' => 'Kecamatan',
                    'struktur_perangkat_daerah_id' => $randomKec?->id,
                    'kode_wilayah' => $randomKec?->kode_wilayah ?? null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Koordinator dan Pengendali (Daltu) Kecamatan',
                    'created_by' => 1,
                ]
            );
        }

        // 5. Pengawas Tim (Dalnis) Kecamatan (6 Orang)
        $dalnisKecNames = [
            'M. Ridwan Hakim, S.E., M.Ak.',
            'Siti Nurhaliza, S.Sos., M.Si.',
            'Joko Purnomo, S.T., M.M.',
            'Eka Pratiwi, S.E., Ak., CA.',
            'Arif Wicaksono, S.H., M.H.',
            'Retno Wulandari, S.E., M.Si.',
        ];

        foreach ($dalnisKecNames as $idx => $name) {
            $num = $idx + 1;
            $randomKec = $kecamatanList->isNotEmpty()
                ? $kecamatanList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "dalnis_kec_{$num}"],
                [
                    'name' => $name,
                    'email' => "dalnis.kec{$num}@eaudit.test",
                    'phone' => sprintf('08241000%04d', $num),
                    'role' => 'Pengawas Tim',
                    'password' => $defaultPassword,
                    'level' => 'Kecamatan',
                    'struktur_perangkat_daerah_id' => $randomKec?->id,
                    'kode_wilayah' => $randomKec?->kode_wilayah ?? null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Pengendali Teknis (Dalnis) Kecamatan',
                    'created_by' => 1,
                ]
            );
        }

        // 6. Ketua Tim (Katim) Kecamatan (7 Orang)
        $ketuaTimKecNames = [
            'Surya Darmawan, S.E.',
            'Dewi Sartika, S.Ak.',
            'Hari Sucipto, S.Kom.',
            'Nadia Octavia, S.E.',
            'Bagas Kurnia, S.Ak.',
            'Gita Paramita, S.E.',
            'Ilham Ramadhani, S.Kom.',
        ];

        foreach ($ketuaTimKecNames as $idx => $name) {
            $num = $idx + 1;
            $randomKec = $kecamatanList->isNotEmpty()
                ? $kecamatanList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "ketuatim_kec_{$num}"],
                [
                    'name' => $name,
                    'email' => "ketuatim.kec{$num}@eaudit.test",
                    'phone' => sprintf('08251000%04d', $num),
                    'role' => 'Ketua Tim',
                    'password' => $defaultPassword,
                    'level' => 'Kecamatan',
                    'struktur_perangkat_daerah_id' => $randomKec?->id,
                    'kode_wilayah' => $randomKec?->kode_wilayah ?? null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Ketua Tim Audit Kecamatan',
                    'created_by' => 1,
                ]
            );
        }
    }
}
