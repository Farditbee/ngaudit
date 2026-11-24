<?php

namespace Database\Factories;

use App\Models\Plotting;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tim>
 */
class TimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_tim' => 'Tim Audit '.strtoupper(fake()->unique()->lexify('???')),
            'lingkup' => 'Nasional',
            'plotting_id' => Plotting::factory(),
            'province_code' => null,
            'daltu_id' => User::factory()->state(['role' => 'Koordinator dan Pengendali']),
            'dalnis_id' => User::factory()->state(['role' => 'Pengawas Tim']),
            'ketua_tim_id' => User::factory()->state(['role' => 'Ketua Tim']),
            'ketua_tim_2_id' => null,
        ];
    }
}
