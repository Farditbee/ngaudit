<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('wilayah') || DB::table('wilayah')->count() > 0) {
            return;
        }

        $csvBase = base_path('vendor/laravolt/indonesia/resources/csv');
        if (! File::isDirectory($csvBase)) {
            return;
        }

        $now = now();
        $provinces = [];
        $cities = [];
        $districts = [];
        $batch = [];

        // 1. Provinces
        $provincesFile = $csvBase.'/provinces.csv';
        if (File::exists($provincesFile) && ($handle = fopen($provincesFile, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $code = trim((string) ($row[0] ?? ''));
                $name = trim((string) ($row[1] ?? ''));
                if ($code === '') {
                    continue;
                }
                $provinces[$code] = $name;
                $batch[] = [
                    'kode_wilayah' => $code,
                    'provinsi' => $name,
                    'kabupaten_kota' => null,
                    'kecamatan' => null,
                    'desa_kelurahan' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            fclose($handle);
        }

        // 2. Cities
        $citiesFile = $csvBase.'/cities.csv';
        if (File::exists($citiesFile) && ($handle = fopen($citiesFile, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $code = trim((string) ($row[0] ?? ''));
                $provCode = trim((string) ($row[1] ?? ''));
                $name = trim((string) ($row[2] ?? ''));
                if ($code === '') {
                    continue;
                }
                $cities[$code] = [
                    'provinsi' => $provinces[$provCode] ?? '',
                    'kabupaten_kota' => $name,
                ];
                $batch[] = [
                    'kode_wilayah' => $code,
                    'provinsi' => $provinces[$provCode] ?? '',
                    'kabupaten_kota' => $name,
                    'kecamatan' => null,
                    'desa_kelurahan' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            fclose($handle);
        }

        // 3. Districts
        $districtsFile = $csvBase.'/districts.csv';
        if (File::exists($districtsFile) && ($handle = fopen($districtsFile, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $code = trim((string) ($row[0] ?? ''));
                $cityCode = trim((string) ($row[1] ?? ''));
                $name = trim((string) ($row[2] ?? ''));
                if ($code === '') {
                    continue;
                }
                $cityData = $cities[$cityCode] ?? ['provinsi' => '', 'kabupaten_kota' => ''];
                $districts[$code] = [
                    'provinsi' => $cityData['provinsi'],
                    'kabupaten_kota' => $cityData['kabupaten_kota'],
                    'kecamatan' => $name,
                ];
                $batch[] = [
                    'kode_wilayah' => $code,
                    'provinsi' => $cityData['provinsi'],
                    'kabupaten_kota' => $cityData['kabupaten_kota'],
                    'kecamatan' => $name,
                    'desa_kelurahan' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 1000) {
                    DB::table('wilayah')->insert($batch);
                    $batch = [];
                }
            }
            fclose($handle);
        }

        if (! empty($batch)) {
            DB::table('wilayah')->insert($batch);
            $batch = [];
        }

        // 4. Villages
        $villagesDir = $csvBase.'/villages';
        if (File::isDirectory($villagesDir)) {
            foreach (File::files($villagesDir) as $file) {
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        $code = trim((string) ($row[0] ?? ''));
                        $distCode = trim((string) ($row[1] ?? ''));
                        $name = trim((string) ($row[2] ?? ''));
                        if ($code === '') {
                            continue;
                        }
                        $distData = $districts[$distCode] ?? [
                            'provinsi' => '',
                            'kabupaten_kota' => '',
                            'kecamatan' => '',
                        ];
                        $batch[] = [
                            'kode_wilayah' => $code,
                            'provinsi' => $distData['provinsi'],
                            'kabupaten_kota' => $distData['kabupaten_kota'],
                            'kecamatan' => $distData['kecamatan'],
                            'desa_kelurahan' => $name,
                            'created_by' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($batch) >= 1000) {
                            DB::table('wilayah')->insert($batch);
                            $batch = [];
                        }
                    }
                    fclose($handle);
                }
            }
        }

        if (! empty($batch)) {
            DB::table('wilayah')->insert($batch);
        }
    }
}
