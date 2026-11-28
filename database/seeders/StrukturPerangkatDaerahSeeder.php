<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StrukturPerangkatDaerahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/fixed_lampung_tengah.csv');

        if (! file_exists($csvPath)) {
            $this->command?->warn("File CSV {$csvPath} tidak ditemukan.");

            return;
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            return;
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return;
        }

        $rows = [];
        $rawRecords = [];

        while (($line = fgetcsv($handle)) !== false) {
            $record = array_combine($header, $line);
            $rawRecords[$record['id']] = $record;
        }
        fclose($handle);

        // Helper untuk mencari nama kecamatan leluhur jika ada
        $findAncestorKecamatan = function (string|int $parentId) use (&$findAncestorKecamatan, $rawRecords): ?string {
            if (empty($parentId) || ! isset($rawRecords[$parentId])) {
                return null;
            }

            $parent = $rawRecords[$parentId];
            if (($parent['jenis_unit'] ?? '') === 'Kecamatan') {
                return $parent['nama_unit'];
            }

            return $findAncestorKecamatan($parent['parent_id'] ?? null);
        };

        $now = now();
        $batch = [];

        foreach ($rawRecords as $id => $record) {
            $parentId = ! empty($record['parent_id']) ? (int) $record['parent_id'] : null;
            $level = (int) $record['level'];
            $jenisUnit = trim($record['jenis_unit']);
            $namaUnit = trim($record['nama_unit']);
            $pimpinan = ! empty($record['pimpinan']) ? trim($record['pimpinan']) : null;

            $ancestorKecamatan = $parentId ? $findAncestorKecamatan($parentId) : null;

            $kecamatanVal = null;
            $desaVal = null;

            if ($jenisUnit === 'Kecamatan') {
                $kecamatanVal = $namaUnit;
            } elseif ($ancestorKecamatan !== null) {
                $kecamatanVal = $ancestorKecamatan;
                $desaVal = $namaUnit;
            }

            // Kode unik unit (format berjenjang LAMPUNG TENGAH 18.02.xxx)
            $kodeUnit = '18.02.'.str_pad((string) $id, 3, '0', STR_PAD_LEFT);
            $kodeWilayah = (string) $id;

            $batch[] = [
                'id' => (int) $id,
                'parent_id' => $parentId,
                'nama_unit' => $namaUnit,
                'level' => $level,
                'jenis_unit' => $jenisUnit,
                'pimpinan' => $pimpinan,
                'kode_unit' => $kodeUnit,
                'kode_wilayah' => $kodeWilayah,
                'provinsi' => 'LAMPUNG',
                'kabupaten_kota' => 'KABUPATEN LAMPUNG TENGAH',
                'kecamatan' => $kecamatanVal,
                'desa_kelurahan' => $desaVal,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 100) {
                DB::table('struktur_perangkat_daerah')->upsert(
                    $batch,
                    ['id'],
                    ['parent_id', 'nama_unit', 'level', 'jenis_unit', 'pimpinan', 'kode_unit', 'kode_wilayah', 'provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan', 'updated_at']
                );
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('struktur_perangkat_daerah')->upsert(
                $batch,
                ['id'],
                ['parent_id', 'nama_unit', 'level', 'jenis_unit', 'pimpinan', 'kode_unit', 'kode_wilayah', 'provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan', 'updated_at']
            );
        }
    }
}
