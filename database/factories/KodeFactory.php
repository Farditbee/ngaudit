<?php

namespace Database\Factories;

use App\Models\Kode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kode>
 */
class KodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis' => Kode::JENIS_TEMUAN,
            'kode' => str_pad((string) fake()->unique()->numberBetween(11, 99), 2, '0', STR_PAD_LEFT),
            'nama' => fake()->sentence(3),
        ];
    }
}
