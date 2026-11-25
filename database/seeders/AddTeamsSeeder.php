<?php

namespace Database\Seeders;

use App\Models\Tim;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddTeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan WIL III memiliki Dalnis (Teguh Santosa diarahkan ke WIL III)
        User::query()->where('id', 32)->update(['plotting_id' => 3]);

        // 2. Siapkan Daltu, Dalnis, dan Katim untuk masing-masing provinsi Jawa Series (31, 33, 34, 35, 36)
        $jawaOfficials = [
            '31' => ['name' => 'DKI Jakarta', 'daltu_id' => 136, 'dalnis_id' => 137, 'katim_id' => 138],
            '33' => ['name' => 'Jawa Tengah', 'daltu_id' => 139, 'dalnis_id' => 140, 'katim_id' => 141],
            '34' => ['name' => 'DI Yogyakarta', 'daltu_id' => 142, 'dalnis_id' => 143, 'katim_id' => 144],
            '35' => ['name' => 'Jawa Timur', 'daltu_id' => 145, 'dalnis_id' => 146, 'katim_id' => 147],
            '36' => ['name' => 'Banten', 'daltu_id' => 148, 'dalnis_id' => 149, 'katim_id' => 150],
        ];

        foreach ($jawaOfficials as $code => $info) {
            User::query()->where('id', $info['daltu_id'])->update([
                'name' => "Daltu {$info['name']}",
                'username' => "daltu_{$code}",
                'role' => 'Koordinator dan Pengendali',
                'level' => 'Wilayah',
                'kode_wilayah' => $code,
                'plotting_id' => null,
                'bidang' => null,
                'jabatan' => 'Koordinator dan Pengendali',
            ]);

            User::query()->where('id', $info['dalnis_id'])->update([
                'name' => "Dalnis {$info['name']}",
                'username' => "dalnis_{$code}",
                'role' => 'Pengawas Tim',
                'level' => 'Wilayah',
                'kode_wilayah' => $code,
                'plotting_id' => null,
                'bidang' => null,
                'jabatan' => 'Pengawas Tim',
            ]);

            User::query()->where('id', $info['katim_id'])->update([
                'name' => "Katim {$info['name']}",
                'username' => "katim_{$code}",
                'role' => 'Ketua Tim',
                'level' => 'Wilayah',
                'kode_wilayah' => $code,
                'plotting_id' => null,
                'bidang' => null,
                'jabatan' => 'Ketua Tim',
            ]);
        }

        // 3. Buat Tim Nasional WIL I (Diponegoro)
        $auditorsWil1 = User::query()->where('role', 'Auditor')->where('plotting_id', 1)->take(4)->pluck('id')->all();
        $timWil1 = Tim::query()->firstOrCreate(
            ['nama_tim' => 'TIM WIL I Diponegoro'],
            [
                'lingkup' => 'Nasional',
                'plotting_id' => 1,
                'province_code' => null,
                'daltu_id' => 17,
                'dalnis_id' => 33,
                'ketua_tim_id' => 22,
                'ketua_tim_2_id' => 29,
                'jumlah_anggota' => count($auditorsWil1),
                'created_by' => 1,
            ]
        );
        $timWil1->auditors()->sync($auditorsWil1);

        // 4. Buat Tim Nasional WIL III (Hasanuddin)
        $auditorsWil3 = User::query()->where('role', 'Auditor')->where('plotting_id', 3)->take(4)->pluck('id')->all();
        $timWil3 = Tim::query()->firstOrCreate(
            ['nama_tim' => 'TIM WIL III Hasanuddin'],
            [
                'lingkup' => 'Nasional',
                'plotting_id' => 3,
                'province_code' => null,
                'daltu_id' => 19,
                'dalnis_id' => 32,
                'ketua_tim_id' => 20,
                'ketua_tim_2_id' => 21,
                'jumlah_anggota' => count($auditorsWil3),
                'created_by' => 1,
            ]
        );
        $timWil3->auditors()->sync($auditorsWil3);

        // 5. Buat Tim Wilayah untuk setiap provinsi Jawa Series
        foreach ($jawaOfficials as $code => $info) {
            $auditors = User::query()
                ->where('role', 'Auditor')
                ->where('kode_wilayah', $code)
                ->pluck('id')
                ->all();

            $tim = Tim::query()->firstOrCreate(
                ['nama_tim' => "TIM Wilayah {$info['name']}"],
                [
                    'lingkup' => 'Wilayah',
                    'plotting_id' => null,
                    'province_code' => $code,
                    'daltu_id' => $info['daltu_id'],
                    'dalnis_id' => $info['dalnis_id'],
                    'ketua_tim_id' => $info['katim_id'],
                    'ketua_tim_2_id' => null,
                    'jumlah_anggota' => count($auditors),
                    'created_by' => 1,
                ]
            );
            $tim->auditors()->sync($auditors);
        }
    }
}
