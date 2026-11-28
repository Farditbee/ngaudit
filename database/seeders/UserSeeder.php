<?php

namespace Database\Seeders;

use App\Models\StrukturPerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123');

        // 1. Pastikan Super Admin tetap ada (ID 1)
        $existingSuperAdmin = User::query()->where('role', 'Super Admin')->first();
        $superAdminPassword = $existingSuperAdmin?->password ?? $defaultPassword;

        User::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => $existingSuperAdmin?->name ?? 'Super Admin',
                'username' => $existingSuperAdmin?->username ?? 'superadmin',
                'email' => $existingSuperAdmin?->email ?? 'admin@test.com',
                'phone' => $existingSuperAdmin?->phone ?? '081100000001',
                'role' => 'Super Admin',
                'password' => $superAdminPassword,
                'level' => null,
                'struktur_perangkat_daerah_id' => null,
                'kode_wilayah' => null,
                'bidang' => null,
                'jabatan' => 'Super Administrator',
                'created_by' => null,
            ]
        );

        // 1b. Pastikan Admin Dinas ada
        User::query()->updateOrCreate(
            ['username' => 'admindinas'],
            [
                'name' => 'Admin Dinas',
                'email' => 'admin.dinas@eaudit.test',
                'phone' => '081199990001',
                'role' => 'Admin Dinas',
                'password' => $defaultPassword,
                'level' => 'Dinas',
                'struktur_perangkat_daerah_id' => null,
                'kode_wilayah' => null,
                'bidang' => null,
                'jabatan' => 'Administrator Tingkat Dinas',
                'created_by' => 1,
            ]
        );

        // 2. 30 Auditor dengan Level Dinas, acak di Dinas/Badan/OPD
        $dinasList = StrukturPerangkatDaerah::dinas()->get();

        $auditorNames = [
            'Budi Santoso',
            'Andi Pratama',
            'Rizky Ramadhan',
            'Fajar Nugroho',
            'Hendra Wijaya',
            'Dimas Saputra',
            'Arif Hidayat',
            'Bayu Setiawan',
            'Reza Pahlevi',
            'Bagus Kurniawan',
            'Ahmad Fauzi',
            'Eko Prasetyo',
            'Dedi Irawan',
            'Wahyu Hidayat',
            'Agus Triyono',
            'Surya Saputra',
            'Taufik Ismail',
            'Yudi Hermawan',
            'Rian Firmansyah',
            'Ilham Maulana',
            'Aditya Nugraha',
            'Bambang Susanto',
            'Guntur Wibowo',
            'Irfan Hakim',
            'Lukman Hakim',
            'Muhammad Ridwan',
            'Nanang Kosim',
            'Rahmat Hidayat',
            'Sigit Purnomo',
            'Yusuf Maulana',
        ];

        foreach ($auditorNames as $idx => $name) {
            $num = $idx + 1;
            $randomDinas = $dinasList->isNotEmpty()
                ? $dinasList[$idx % $dinasList->count()]
                : null;

            User::query()->updateOrCreate(
                ['username' => "auditor{$num}"],
                [
                    'name' => $name,
                    'email' => "auditor{$num}@eaudit.test",
                    'phone' => sprintf('08120000%04d', $num),
                    'role' => 'Auditor',
                    'password' => $defaultPassword,
                    'level' => 'Dinas',
                    'struktur_perangkat_daerah_id' => $randomDinas?->id,
                    'kode_wilayah' => null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Auditor',
                    'created_by' => 1,
                ]
            );
        }

        // 3. Koordinator dan Pengendali (Daltu) - Level Dinas (6 user)
        $daltuNames = [
            'Dr. H. Slamet Riyadi, M.M.',
            'Drs. Bambang Sutrisno, M.Si.',
            'Dra. Sri Wahyuni, M.Ak.',
            'Ir. Joko Susilo, M.T.',
            'Hendra Gunawan, S.E., M.Si., Ak.',
            'Ratna Sari, S.E., M.M., CA.',
        ];

        foreach ($daltuNames as $idx => $name) {
            $num = $idx + 1;
            $randomDinas = $dinasList->isNotEmpty()
                ? $dinasList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "daltu{$num}"],
                [
                    'name' => $name,
                    'email' => "daltu{$num}@eaudit.test",
                    'phone' => sprintf('08130000%04d', $num),
                    'role' => 'Koordinator dan Pengendali',
                    'password' => $defaultPassword,
                    'level' => 'Dinas',
                    'struktur_perangkat_daerah_id' => $randomDinas?->id,
                    'kode_wilayah' => null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Koordinator dan Pengendali (Daltu)',
                    'created_by' => 1,
                ]
            );
        }

        // 4. Pengawas Tim (Dalnis / Pengendali Teknis) - Level Dinas (8 user)
        $dalnisNames = [
            'Agus Kurniawan, S.E., M.Ak.',
            'Dewi Anggraeni, S.Sos., M.Si.',
            'Tri Handoko, S.T., M.M.',
            'Nur Hidayati, S.E., Ak., CA.',
            'Danang Prasetyo, S.H., M.H.',
            'Maya Indah Puspita, S.E., M.Si.',
            'Ahmad Syahrir, S.E., M.M.',
            'Indah Permatasari, S.E., Ak.',
        ];

        foreach ($dalnisNames as $idx => $name) {
            $num = $idx + 1;
            $randomDinas = $dinasList->isNotEmpty()
                ? $dinasList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "dalnis{$num}"],
                [
                    'name' => $name,
                    'email' => "dalnis{$num}@eaudit.test",
                    'phone' => sprintf('08140000%04d', $num),
                    'role' => 'Pengawas Tim',
                    'password' => $defaultPassword,
                    'level' => 'Dinas',
                    'struktur_perangkat_daerah_id' => $randomDinas?->id,
                    'kode_wilayah' => null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Pengendali Teknis (Dalnis)',
                    'created_by' => 1,
                ]
            );
        }

        // 5. Ketua Tim - Level Dinas (8 user)
        $ketuaTimNames = [
            'Fajar Surya Pratama, S.E.',
            'Rina Novitasari, S.E.',
            'Dedi Supriyadi, S.Kom.',
            'Anisa Rahmawati, S.Ak.',
            'Wawan Setiawan, S.E.',
            'Fitri Handayani, S.Ak.',
            'Gunawan Wibisono, S.E.',
            'Citra Lestari, S.Ak.',
        ];

        foreach ($ketuaTimNames as $idx => $name) {
            $num = $idx + 1;
            $randomDinas = $dinasList->isNotEmpty()
                ? $dinasList->random()
                : null;

            User::query()->updateOrCreate(
                ['username' => "ketuatim{$num}"],
                [
                    'name' => $name,
                    'email' => "ketuatim{$num}@eaudit.test",
                    'phone' => sprintf('08150000%04d', $num),
                    'role' => 'Ketua Tim',
                    'password' => $defaultPassword,
                    'level' => 'Dinas',
                    'struktur_perangkat_daerah_id' => $randomDinas?->id,
                    'kode_wilayah' => null,
                    'bidang' => User::BIDANGS[$idx % count(User::BIDANGS)],
                    'jabatan' => 'Ketua Tim Audit',
                    'created_by' => 1,
                ]
            );
        }

        // 6. Akun Auditee Representatif (Level Dinas & Kecamatan)
        $dishub = StrukturPerangkatDaerah::query()->where('nama_unit', 'like', '%Perhubungan%')->first();
        $uptDishub = $dishub ? StrukturPerangkatDaerah::query()->where('parent_id', $dishub->id)->where('nama_unit', 'like', '%UPT%')->first() : null;
        $dinkes = StrukturPerangkatDaerah::query()->where('nama_unit', 'like', '%Kesehatan%')->first();
        $kecBs = StrukturPerangkatDaerah::query()->where('nama_unit', 'like', '%Bandar Surabaya%')->where('level', 1)->first();
        $seksiBs = $kecBs ? StrukturPerangkatDaerah::query()->where('parent_id', $kecBs->id)->where('nama_unit', 'like', '%Ketentraman%')->first() : null;
        $kecBm = StrukturPerangkatDaerah::query()->where('nama_unit', 'like', '%Bandar Mataram%')->where('level', 1)->first();

        $auditeeAccounts = [
            [
                'username' => 'auditee_dishub',
                'name' => 'Auditee Dinas Perhubungan',
                'email' => 'auditee.dishub@eaudit.test',
                'phone' => '081600000001',
                'level' => 'Dinas',
                'unit_id' => $dishub?->id,
                'bidang' => 'Operasional',
                'jabatan' => 'Pengelola Tindak Lanjut Dinas',
            ],
            [
                'username' => 'auditee_upt_dishub',
                'name' => 'Auditee UPT Perhubungan',
                'email' => 'auditee.uptdishub@eaudit.test',
                'phone' => '081600000002',
                'level' => 'Dinas',
                'unit_id' => $uptDishub?->id,
                'bidang' => 'Operasional',
                'jabatan' => 'Staf UPT Perhubungan',
            ],
            [
                'username' => 'auditee_dinkes',
                'name' => 'Auditee Dinas Kesehatan',
                'email' => 'auditee.dinkes@eaudit.test',
                'phone' => '081600000003',
                'level' => 'Dinas',
                'unit_id' => $dinkes?->id,
                'bidang' => 'Manajemen Anggaran dan Keuangan',
                'jabatan' => 'Pengelola Tindak Lanjut Dinas',
            ],
            [
                'username' => 'auditee_kec_bs',
                'name' => 'Auditee Kec. Bandar Surabaya',
                'email' => 'auditee.kecbs@eaudit.test',
                'phone' => '081600000004',
                'level' => 'Kecamatan',
                'unit_id' => $kecBs?->id,
                'bidang' => 'Operasional',
                'jabatan' => 'Pengelola Tindak Lanjut Kecamatan',
            ],
            [
                'username' => 'auditee_seksi_bs',
                'name' => 'Auditee Seksi Trantib Kec. Bandar Surabaya',
                'email' => 'auditee.seksibs@eaudit.test',
                'phone' => '081600000005',
                'level' => 'Kecamatan',
                'unit_id' => $seksiBs?->id,
                'bidang' => 'Operasional',
                'jabatan' => 'Kasi Trantib Kec. Bandar Surabaya',
            ],
            [
                'username' => 'auditee_kec_bm',
                'name' => 'Auditee Kec. Bandar Mataram',
                'email' => 'auditee.kecbm@eaudit.test',
                'phone' => '081600000006',
                'level' => 'Kecamatan',
                'unit_id' => $kecBm?->id,
                'bidang' => 'Operasional',
                'jabatan' => 'Pengelola Tindak Lanjut Kecamatan',
            ],
        ];

        foreach ($auditeeAccounts as $acc) {
            User::query()->updateOrCreate(
                ['username' => $acc['username']],
                [
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'phone' => $acc['phone'],
                    'role' => 'Auditee',
                    'password' => $defaultPassword,
                    'level' => $acc['level'],
                    'struktur_perangkat_daerah_id' => $acc['unit_id'],
                    'kode_wilayah' => null,
                    'bidang' => $acc['bidang'],
                    'jabatan' => $acc['jabatan'],
                    'created_by' => 1,
                ]
            );
        }

        $this->call(UserKecamatanSeeder::class);
    }
}
