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
        $daltuAll = User::query()->where('role', 'Koordinator dan Pengendali')->orderBy('id')->get();
        $dalnisAll = User::query()->where('role', 'Pengawas Tim')->orderBy('id')->get();
        $katimAll = User::query()->where('role', 'Ketua Tim')->orderBy('id')->get();
        $auditorAll = User::query()->where('role', 'Auditor')->orderBy('id')->get();

        $daltuDinas = $daltuAll->where('level', 'Dinas')->values();
        $dalnisDinas = $dalnisAll->where('level', 'Dinas')->values();
        $katimDinas = $katimAll->where('level', 'Dinas')->values();
        $auditorDinas = $auditorAll->where('level', 'Dinas')->values();

        $daltuDinas = $daltuDinas->isNotEmpty() ? $daltuDinas : $daltuAll;
        $dalnisDinas = $dalnisDinas->isNotEmpty() ? $dalnisDinas : $dalnisAll;
        $katimDinas = $katimDinas->isNotEmpty() ? $katimDinas : $katimAll;
        $auditorDinas = $auditorDinas->isNotEmpty() ? $auditorDinas : $auditorAll;

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

            $daltu = $daltuDinas->get($idx % max(1, $daltuDinas->count()));
            $dalnis = $dalnisDinas->get($idx % max(1, $dalnisDinas->count()));
            $katim = $katimDinas->get($idx % max(1, $katimDinas->count()));

            $assignedAuditors = $auditorDinas->slice($auditorOffset, $def['auditor_count']);
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

        // Seeder Tim Audit Khusus Lingkup Kecamatan
        $daltuKec = $daltuAll->where('level', 'Kecamatan')->values();
        $dalnisKec = $dalnisAll->where('level', 'Kecamatan')->values();
        $katimKec = $katimAll->where('level', 'Kecamatan')->values();
        $auditorKec = $auditorAll->where('level', 'Kecamatan')->values();

        if ($auditorKec->isNotEmpty()) {
            $timKecamatanDefinitions = [
                [
                    'nama_tim' => 'Tim Audit Kecamatan 01 - Kec. Gunung Sugih',
                    'target_spd' => 'Kecamatan Gunung Sugih',
                    'auditor_count' => 3,
                ],
                [
                    'nama_tim' => 'Tim Audit Kecamatan 02 - Kec. Terbanggi Besar',
                    'target_spd' => 'Kecamatan Terbanggi Besar',
                    'auditor_count' => 3,
                ],
                [
                    'nama_tim' => 'Tim Audit Kecamatan 03 - Kec. Seputih Agung',
                    'target_spd' => 'Kecamatan Seputih Agung',
                    'auditor_count' => 3,
                ],
                [
                    'nama_tim' => 'Tim Audit Kecamatan 04 - Kec. Bumi Ratu Nuban',
                    'target_spd' => 'Kecamatan Bumi Ratu Nuban',
                    'auditor_count' => 3,
                ],
                [
                    'nama_tim' => 'Tim Audit Kecamatan 05 - Kec. Bekri',
                    'target_spd' => 'Kecamatan Bekri',
                    'auditor_count' => 2,
                ],
                [
                    'nama_tim' => 'Tim Audit Kecamatan 06 - Kec. Kalirejo',
                    'target_spd' => 'Kecamatan Kalirejo',
                    'auditor_count' => 3,
                ],
            ];

            $auditorKecOffset = 0;

            foreach ($timKecamatanDefinitions as $kIdx => $kDef) {
                $spdKec = StrukturPerangkatDaerah::query()
                    ->where('nama_unit', 'LIKE', '%'.$kDef['target_spd'].'%')
                    ->first();

                $daltu = $daltuKec->isNotEmpty() ? $daltuKec->get($kIdx % $daltuKec->count()) : null;
                $dalnis = $dalnisKec->isNotEmpty() ? $dalnisKec->get($kIdx % $dalnisKec->count()) : null;
                $katim = $katimKec->isNotEmpty() ? $katimKec->get($kIdx % $katimKec->count()) : null;

                $assignedAuditors = $auditorKec->slice($auditorKecOffset, $kDef['auditor_count']);
                $auditorKecOffset += $kDef['auditor_count'];

                $tim = Tim::query()->updateOrCreate(
                    ['nama_tim' => $kDef['nama_tim']],
                    [
                        'lingkup' => 'Kecamatan',
                        'struktur_perangkat_daerah_id' => $spdKec?->id,
                        'plotting_id' => $spdKec?->id,
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
}
