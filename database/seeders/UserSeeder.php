<?php

namespace Database\Seeders;

use App\Models\Plotting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Pastikan data Plotting WIL I - WIL IV tersedia
        $plottings = Plotting::query()->orderBy('id')->get();
        if ($plottings->isEmpty()) {
            $provinceIds = DB::table('wilayah')
                ->whereRaw('CHAR_LENGTH(kode_wilayah) = 2')
                ->orderBy('kode_wilayah')
                ->pluck('id')
                ->all();
            $chunks = ! empty($provinceIds) ? array_chunk($provinceIds, max(1, (int) ceil(count($provinceIds) / 4))) : [];

            foreach (['WIL I', 'WIL II', 'WIL III', 'WIL IV'] as $idx => $plotName) {
                $plotting = Plotting::query()->firstOrCreate(['plot' => $plotName]);
                if (! empty($chunks[$idx])) {
                    $plotting->provinces()->syncWithoutDetaching($chunks[$idx]);
                }
            }

            $plottings = Plotting::query()->orderBy('id')->get();
        }

        // Ambil 1 desa lengkap dengan hirarki kecamatan, kabupaten, dan provinsi beserta namanya
        $sharedVillage = DB::table('wilayah')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 10')
            ->select([
                DB::raw('SUBSTRING(kode_wilayah, 1, 2) as province_code'),
                'provinsi as province_name',
                DB::raw('SUBSTRING(kode_wilayah, 1, 4) as city_code'),
                'kabupaten_kota as city_name',
                DB::raw('SUBSTRING(kode_wilayah, 1, 6) as district_code'),
                'kecamatan as district_name',
                'kode_wilayah as village_code',
                'desa_kelurahan as village_name',
            ])
            ->orderBy('kode_wilayah')
            ->first();

        // Fallback jika tabel wilayah belum di-seed
        if (! $sharedVillage) {
            DB::table('wilayah')->updateOrInsert(['kode_wilayah' => '32'], ['provinsi' => 'JAWA BARAT']);
            DB::table('wilayah')->updateOrInsert(['kode_wilayah' => '3209'], ['provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON']);
            DB::table('wilayah')->updateOrInsert(['kode_wilayah' => '320919'], ['provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON', 'kecamatan' => 'WERU']);
            DB::table('wilayah')->updateOrInsert(['kode_wilayah' => '3209192004'], ['provinsi' => 'JAWA BARAT', 'kabupaten_kota' => 'KABUPATEN CIREBON', 'kecamatan' => 'WERU', 'desa_kelurahan' => 'SETU WETAN']);

            $sharedVillage = (object) [
                'province_code' => '32',
                'province_name' => 'JAWA BARAT',
                'city_code' => '3209',
                'city_name' => 'KABUPATEN CIREBON',
                'district_code' => '320919',
                'district_name' => 'WERU',
                'village_code' => '3209192004',
                'village_name' => 'SETU WETAN',
            ];
        }

        // 1. 1 Super Admin (ID 1)
        User::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@eaudit.test',
                'phone' => '081100000001',
                'role' => 'Super Admin',
                'password' => $defaultPassword,
                'level' => null,
                'plotting_id' => null,
                'kode_wilayah' => null,
                'bidang' => null,
                'jabatan' => 'Super Administrator',
                'created_by' => null,
            ]
        );

        // 2. 10 Auditor dengan nama orang & WIL bebas (Level Nasional)
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
        ];

        foreach ($auditorNames as $idx => $personName) {
            $i = $idx + 1;
            $randomPlot = $plottings->random();

            User::query()->updateOrCreate(
                ['username' => "auditor{$i}"],
                [
                    'name' => $personName,
                    'email' => "auditor{$i}@eaudit.test",
                    'phone' => sprintf('0812000000%02d', $i),
                    'role' => 'Auditor',
                    'password' => $defaultPassword,
                    'level' => 'Nasional',
                    'plotting_id' => $randomPlot->id,
                    'kode_wilayah' => null,
                    'bidang' => null,
                    'jabatan' => 'Auditor',
                    'created_by' => 1,
                ]
            );
        }

        // 3. 5 Auditee dengan Level Desa yang sama untuk masing-masing Bidang
        // Nama menggunakan nama wilayah level yang digunakan + bidang (misal: Keude Bakongan Operasional)
        $bidangs = User::BIDANGS;
        $regionTitle = Str::title(strtolower((string) $sharedVillage->village_name));

        for ($i = 1; $i <= 5; $i++) {
            $bidang = $bidangs[($i - 1) % count($bidangs)];
            $auditeeName = "{$regionTitle} {$bidang}";

            User::query()->updateOrCreate(
                ['username' => "auditee{$i}"],
                [
                    'name' => $auditeeName,
                    'email' => "auditee{$i}@eaudit.test",
                    'phone' => sprintf('0813000000%02d', $i),
                    'role' => 'Auditee',
                    'password' => $defaultPassword,
                    'level' => 'Desa',
                    'plotting_id' => null,
                    'kode_wilayah' => $sharedVillage->village_code,
                    'bidang' => $bidang,
                    'jabatan' => "Kepala Bidang {$bidang}",
                    'created_by' => 1,
                ]
            );
        }

        // 4. 3 Koordinator dan Pengendali dengan nama orang
        $koordinatorNames = [
            'Irwan Gunawan',
            'Bambang Suryono',
            'Agus Hermawan',
        ];

        foreach ($koordinatorNames as $idx => $personName) {
            $i = $idx + 1;
            $plot = $plottings[$idx % $plottings->count()];

            User::query()->updateOrCreate(
                ['username' => "koordinator{$i}"],
                [
                    'name' => $personName,
                    'email' => "koordinator{$i}@eaudit.test",
                    'phone' => sprintf('0814000000%02d', $i),
                    'role' => 'Koordinator dan Pengendali',
                    'password' => $defaultPassword,
                    'level' => 'Nasional',
                    'plotting_id' => $plot->id,
                    'kode_wilayah' => null,
                    'bidang' => null,
                    'jabatan' => 'Koordinator dan Pengendali',
                    'created_by' => 1,
                ]
            );
        }

        // 5. 10 Ketua Tim dengan nama orang
        $ketuaTimNames = [
            'Dedi Kurnia',
            'Eko Prasetyo',
            'Lukman Hakim',
            'Surya Darma',
            'Taufik Hidayat',
            'Wahyu Wibowo',
            'Yudianto',
            'Zulfikar Rahman',
            'Rangga Mahendra',
            'Aditya Permana',
        ];

        foreach ($ketuaTimNames as $idx => $personName) {
            $i = $idx + 1;
            $randomPlot = $plottings->random();

            User::query()->updateOrCreate(
                ['username' => "ketuatim{$i}"],
                [
                    'name' => $personName,
                    'email' => "ketuatim{$i}@eaudit.test",
                    'phone' => sprintf('0815000000%02d', $i),
                    'role' => 'Ketua Tim',
                    'password' => $defaultPassword,
                    'level' => 'Nasional',
                    'plotting_id' => $randomPlot->id,
                    'kode_wilayah' => null,
                    'bidang' => null,
                    'jabatan' => 'Ketua Tim Audit',
                    'created_by' => 1,
                ]
            );
        }

        // 6. 5 Pengawas Tim dengan nama orang
        $pengawasNames = [
            'Rahmat Hidayatullah',
            'Gunawan Setiadi',
            'Teguh Santosa',
            'Heru Purnomo',
            'Firman Syahputra',
        ];

        foreach ($pengawasNames as $idx => $personName) {
            $i = $idx + 1;
            $randomPlot = $plottings->random();

            User::query()->updateOrCreate(
                ['username' => "pengawastim{$i}"],
                [
                    'name' => $personName,
                    'email' => "pengawastim{$i}@eaudit.test",
                    'phone' => sprintf('0816000000%02d', $i),
                    'role' => 'Pengawas Tim',
                    'password' => $defaultPassword,
                    'level' => 'Nasional',
                    'plotting_id' => $randomPlot->id,
                    'kode_wilayah' => null,
                    'bidang' => null,
                    'jabatan' => 'Pengawas Tim Audit',
                    'created_by' => 1,
                ]
            );
        }
    }
}
