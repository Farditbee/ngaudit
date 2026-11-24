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
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $defaultPassword = Hash::make('Password123');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Pastikan data Plotting WIL I - WIL IV tersedia
        $plottings = Plotting::query()->orderBy('id')->get();
        if ($plottings->isEmpty()) {
            $provinceIds = DB::table($prefix.'provinces')->orderBy('code')->pluck('id')->all();
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
        $sharedVillage = DB::table($prefix.'villages as v')
            ->join($prefix.'districts as d', 'd.code', '=', 'v.district_code')
            ->join($prefix.'cities as c', 'c.code', '=', 'd.city_code')
            ->join($prefix.'provinces as p', 'p.code', '=', 'c.province_code')
            ->select([
                'p.code as province_code',
                'p.name as province_name',
                'c.code as city_code',
                'c.name as city_name',
                'd.code as district_code',
                'd.name as district_name',
                'v.code as village_code',
                'v.name as village_name',
            ])
            ->orderBy('v.code')
            ->first();

        // Fallback jika tabel wilayah belum di-seed
        if (! $sharedVillage) {
            DB::table($prefix.'provinces')->updateOrInsert(['code' => '32'], ['name' => 'JAWA BARAT']);
            DB::table($prefix.'cities')->updateOrInsert(['code' => '3209'], ['province_code' => '32', 'name' => 'KABUPATEN CIREBON']);
            DB::table($prefix.'districts')->updateOrInsert(['code' => '320919'], ['city_code' => '3209', 'name' => 'WERU']);
            DB::table($prefix.'villages')->updateOrInsert(['code' => '3209192004'], ['district_code' => '320919', 'name' => 'SETU WETAN']);

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
                'province_code' => null,
                'city_code' => null,
                'district_code' => null,
                'village_code' => null,
                'bidang' => null,
                'jabatan' => 'Super Administrator',
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
                    'province_code' => null,
                    'city_code' => null,
                    'district_code' => null,
                    'village_code' => null,
                    'bidang' => null,
                    'jabatan' => 'Auditor',
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
                    'province_code' => $sharedVillage->province_code,
                    'city_code' => $sharedVillage->city_code,
                    'district_code' => $sharedVillage->district_code,
                    'village_code' => $sharedVillage->village_code,
                    'bidang' => $bidang,
                    'jabatan' => "Kepala Bidang {$bidang}",
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
                    'province_code' => null,
                    'city_code' => null,
                    'district_code' => null,
                    'village_code' => null,
                    'bidang' => null,
                    'jabatan' => 'Koordinator dan Pengendali',
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
                    'province_code' => null,
                    'city_code' => null,
                    'district_code' => null,
                    'village_code' => null,
                    'bidang' => null,
                    'jabatan' => 'Ketua Tim Audit',
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
                    'province_code' => null,
                    'city_code' => null,
                    'district_code' => null,
                    'village_code' => null,
                    'bidang' => null,
                    'jabatan' => 'Pengawas Tim Audit',
                ]
            );
        }
    }
}
