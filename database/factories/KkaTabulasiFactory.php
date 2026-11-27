<?php

namespace Database\Factories;

use App\Models\Kka;
use App\Models\KkaTabulasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KkaTabulasi>
 */
class KkaTabulasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kka_id' => Kka::factory(),
            'jenis_tabulasi' => 'Temuan',
            'bidang' => 'Operasional',
            'nomor' => '01',
            'tipe_temuan' => 'Baru',
            'uraian_temuan' => '<p>Uraian temuan</p>',
            'status' => 'Draft',
        ];
    }
}
