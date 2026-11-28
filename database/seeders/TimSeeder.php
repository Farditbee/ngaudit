<?php

namespace Database\Seeders;

use App\Models\StrukturPerangkatDaerah;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daltuUsers = User::query()->where('role', 'Koordinator dan Pengendali')->orderBy('id')->get();
        $dalnisUsers = User::query()->where('role', 'Pengawas Tim')->orderBy('id')->get();
        $katimUsers = User::query()->where('role', 'Ketua Tim')->orderBy('id')->get();
        $auditorUsers = User::query()->where('role', 'Auditor')->orderBy('id')->get();

        $timDefinitions = [
            [
                'nama_tim' => 'Tim Audit 01 - Dinas Kesehatan',
                'target_spd' => 'Dinas Kesehatan',
                'auditor_count' => 3,
            ],
            [
                'nama_tim' => 'Tim Audit 02 - Dinas Pendidikan dan Kebudayaan',
                'target_spd' => 'Dinas Pendidikan dan Kebudayaan',
                'auditor_count' => 4,
            ],
            [
                'nama_tim' => 'Tim Audit 03 - Dinas Bina Marga dan Bina Konstruksi',
                'target_spd' => 'Dinas Bina Marga dan Bina Konstruksi',
                'auditor_count' => 5,
            ],
            [
                'nama_tim' => 'Tim Audit 04 - Dinas Sumber Daya Air',
                'target_spd' => 'Dinas Sumber Daya Air',
                'auditor_count' => 3,
            ],
            [
                'nama_tim' => 'Tim Audit 05 - Dinas Lingkungan Hidup',
                'target_spd' => 'Dinas Lingkungan Hidup',
                'auditor_count' => 6,
            ],
            [
                'nama_tim' => 'Tim Audit 06 - Dinas Perhubungan',
                'target_spd' => 'Dinas Perhubungan',
                'auditor_count' => 4,
            ],
            [
                'nama_tim' => 'Tim Audit 07 - Badan Pengelolaan Keuangan dan Aset Daerah',
                'target_spd' => 'Badan Pengelolaan Keuangan dan Aset Daerah',
                'auditor_count' => 5,
            ],
        ];

        $auditorOffset = 0;

        foreach ($timDefinitions as $idx => $def) {
            $spd = StrukturPerangkatDaerah::query()
                ->where('nama_unit', 'LIKE', '%'.$def['target_spd'].'%')
                ->first();

            $daltu = $daltuUsers->get($idx % $daltuUsers->count());
            $dalnis = $dalnisUsers->get($idx % $dalnisUsers->count());
            $katim = $katimUsers->get($idx % $katimUsers->count());

            $assignedAuditors = $auditorUsers->slice($auditorOffset, $def['auditor_count']);
            $auditorOffset += $def['auditor_count'];

            $tim = Tim::query()->updateOrCreate(
                ['nama_tim' => $def['nama_tim']],
                [
                    'lingkup' => 'Dinas',
                    'struktur_perangkat_daerah_id' => $spd?->id,
                    'plotting_id' => $spd?->id,
                    'province_code' => null,
                    'daltu_id' => $daltu?->id,
                    'dalnis_id' => $dalnis?->id,
                    'ketua_tim_id' => $katim?->id,
                    'ketua_tim_2_id' => null,
                    'jumlah_anggota' => $assignedAuditors->count(),
                    'created_by' => 1,
                ]
            );

            if ($assignedAuditors->isNotEmpty()) {
                $tim->auditors()->sync($assignedAuditors->pluck('id')->toArray());
            }
        }
    }
}
