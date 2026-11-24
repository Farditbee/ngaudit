<?php

namespace Database\Factories;

use App\Models\Plotting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plotting>
 */
class PlottingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plot' => 'WIL '.strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
