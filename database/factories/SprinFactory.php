<?php

namespace Database\Factories;

use App\Models\Sprin;
use App\Models\Tim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sprin>
 */
class SprinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tim = Tim::factory()->create();
        $tglMulai = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'no_sprin' => 'SPRIN-'.fake()->unique()->numerify('####').'/2026',
            'no_surat_pemberitahuan' => 'SP-'.fake()->unique()->numerify('####').'/2026',
            'tgl_mulai' => $tglMulai->format('Y-m-d'),
            'tgl_akhir' => (clone $tglMulai)->modify('+14 days')->format('Y-m-d'),
            'plotting_id' => $tim->plotting_id,
            'kode_wilayah' => $tim->province_code,
            'tim_id' => $tim->id,
            'katim' => $tim->ketua_tim_id,
            'katim_2' => $tim->ketua_tim_2_id,
            'dalnis' => $tim->dalnis_id,
            'daltu' => $tim->daltu_id,
            'tahap' => fake()->randomElement(Sprin::TAHAPS),
            'sprin_tipe' => $tim->lingkup,
            'keterangan' => fake()->sentence(),
        ];
    }
}
