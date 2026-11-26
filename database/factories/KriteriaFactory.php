<?php

namespace Database\Factories;

use App\Models\Kriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kriteria>
 */
class KriteriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->sentence(5),
            'is_opsnal' => fake()->boolean(),
            'is_logistik' => fake()->boolean(),
            'is_sdm' => fake()->boolean(),
            'is_garkeu' => fake()->boolean(),
        ];
    }
}
