<?php

namespace Database\Factories;

use App\Models\Kode;
use App\Models\SubKode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubKode>
 */
class SubKodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_id' => Kode::factory(),
            'kode' => '99.'.str_pad((string) fake()->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'nama' => fake()->sentence(4),
        ];
    }
}
