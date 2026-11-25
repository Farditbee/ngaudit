<?php

namespace Database\Seeders;

use App\Models\Plotting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddAuditorAndAuditeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123');

        $plottings = Plotting::query()->orderBy('id')->get();
        if ($plottings->isEmpty()) {
            $plottings = collect([Plotting::query()->firstOrCreate(['plot' => 'WIL I'])]);
        }

        // 1. Daftar nama bebas untuk 49 Auditor (Sebagian Wilayah Jawa Series, sebagian Nasional)
        $jawaProvinces = ['31', '32', '33', '34', '35', '36'];

        $auditorNames = [
            'Achmad Fauzi', 'Ahmad Dahlan', 'Ali Imron', 'Anjar Wibisono', 'Anton Sujarwo',
            'Ardiansyah Pratama', 'Arief Wicaksono', 'Arya Wiguna', 'Bagus Prasetyo', 'Bambang Trihatmodjo',
            'Banu Setyawan', 'Bayu Anggoro', 'Candra Wijaya', 'Danang Joyo', 'Denny Hendrawan',
            'Derry Sulistyo', 'Dharmawan Saputra', 'Dian Nugraha', 'Dwi Cahyono', 'Eka Ramadhan',
            'Farhan Alamsyah', 'Galih Permadi', 'Gilang Ramadhan', 'Guruh Soekarno', 'Hadi Purnomo',
            'Haris Munandar', 'Hendra Gunawan', 'Heru Santoso', 'Ihsan Kamil', 'Imam Bonjol',
            'Indra Kusuma', 'Iqbal Tawakal', 'Joko Susilo', 'Krisna Murti', 'Kurniawan Dwi',
            'Lukman Sardi', 'Mochammad Hatta', 'Muhamad Ilham', 'Nanang Qosim', 'Oki Setiawan',
            'Panji Gumilang', 'Rahmat Hidayat', 'Rangga Sasana', 'Ridwan Kamil', 'Rifki Fauzan',
            'Roni Paslah', 'Satria Baja', 'Sigit Purnomo', 'Teguh Prakoso',
        ];

        $currentAuditorIndex = 11;
        foreach ($auditorNames as $idx => $personName) {
            $isJawa = $idx < 24; // 24 auditor untuk Jawa series (4 per provinsi)
            $provinceCode = $isJawa ? $jawaProvinces[$idx % count($jawaProvinces)] : null;
            $randomPlot = ! $isJawa ? $plottings->random() : null;

            $existing = User::query()->where('username', "auditor{$currentAuditorIndex}")->first();
            if ($existing) {
                $existing->update([
                    'level' => $isJawa ? 'Wilayah' : 'Nasional',
                    'plotting_id' => $randomPlot?->id,
                    'kode_wilayah' => $provinceCode,
                ]);
                $currentAuditorIndex++;

                continue;
            }

            $username = "auditor{$currentAuditorIndex}";

            User::query()->create([
                'name' => $personName,
                'username' => $username,
                'email' => "{$username}@eaudit.test",
                'phone' => sprintf('0812%08d', 1000 + $currentAuditorIndex),
                'role' => 'Auditor',
                'password' => $defaultPassword,
                'level' => $isJawa ? 'Wilayah' : 'Nasional',
                'plotting_id' => $randomPlot?->id,
                'kode_wilayah' => $provinceCode,
                'bidang' => null,
                'jabatan' => 'Auditor',
                'created_by' => 1,
            ]);

            $currentAuditorIndex++;
        }

        // 2. Daftar wilayah untuk 50 Auditee (Desa, Kecamatan, Kabupaten, Provinsi)
        $bidangs = User::BIDANGS;
        $existingCodes = User::query()->whereNotNull('kode_wilayah')->pluck('kode_wilayah')->all();

        // Ambil 25 Desa
        $desas = DB::table('wilayah')
            ->whereNotNull('desa_kelurahan')
            ->whereNotIn('kode_wilayah', $existingCodes)
            ->take(25)
            ->get(['kode_wilayah', 'desa_kelurahan']);

        // Ambil 10 Kecamatan
        $kecs = DB::table('wilayah')
            ->whereNull('desa_kelurahan')
            ->whereNotNull('kecamatan')
            ->whereNotIn('kode_wilayah', $existingCodes)
            ->take(10)
            ->get(['kode_wilayah', 'kecamatan']);

        // Ambil 10 Kabupaten/Kota
        $kabs = DB::table('wilayah')
            ->whereNull('kecamatan')
            ->whereNotNull('kabupaten_kota')
            ->whereNotIn('kode_wilayah', $existingCodes)
            ->take(10)
            ->get(['kode_wilayah', 'kabupaten_kota']);

        // Ambil 5 Provinsi
        $provs = DB::table('wilayah')
            ->whereNull('kabupaten_kota')
            ->whereNotNull('provinsi')
            ->whereNotIn('kode_wilayah', $existingCodes)
            ->take(5)
            ->get(['kode_wilayah', 'provinsi']);

        $auditeeConfigs = [];
        foreach ($desas as $d) {
            $auditeeConfigs[] = [
                'level' => 'Desa',
                'region_name' => Str::title(strtolower((string) $d->desa_kelurahan)),
                'kode_wilayah' => $d->kode_wilayah,
            ];
        }
        foreach ($kecs as $k) {
            $auditeeConfigs[] = [
                'level' => 'Kecamatan',
                'region_name' => Str::title(strtolower((string) $k->kecamatan)),
                'kode_wilayah' => $k->kode_wilayah,
            ];
        }
        foreach ($kabs as $kb) {
            $auditeeConfigs[] = [
                'level' => 'Kabupaten',
                'region_name' => Str::title(strtolower((string) $kb->kabupaten_kota)),
                'kode_wilayah' => $kb->kode_wilayah,
            ];
        }
        foreach ($provs as $p) {
            $auditeeConfigs[] = [
                'level' => 'Provinsi',
                'region_name' => Str::title(strtolower((string) $p->provinsi)),
                'kode_wilayah' => $p->kode_wilayah,
            ];
        }

        $currentAuditeeIndex = 6;
        foreach ($auditeeConfigs as $idx => $cfg) {
            while (User::query()->where('username', "auditee{$currentAuditeeIndex}")->exists()) {
                $currentAuditeeIndex++;
            }

            $bidang = $bidangs[$idx % count($bidangs)];
            $name = "{$cfg['region_name']} {$bidang}";
            $username = "auditee{$currentAuditeeIndex}";

            User::query()->create([
                'name' => $name,
                'username' => $username,
                'email' => "{$username}@eaudit.test",
                'phone' => sprintf('0813%08d', 1000 + $currentAuditeeIndex),
                'role' => 'Auditee',
                'password' => $defaultPassword,
                'level' => $cfg['level'],
                'plotting_id' => null,
                'kode_wilayah' => $cfg['kode_wilayah'],
                'bidang' => $bidang,
                'jabatan' => "Kepala Bidang {$bidang}",
                'created_by' => 1,
            ]);

            $currentAuditeeIndex++;
        }
    }
}
