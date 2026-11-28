<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            StrukturPerangkatDaerahSeeder::class,
            UserSeeder::class,
            UserKecamatanSeeder::class,
            KodeSeeder::class,
            KriteriaSeeder::class,
            TemplatePkaSeeder::class,
            TemplateKkaSeeder::class,
            TemplateLangkahKerjaSeeder::class,
            TimSeeder::class,
            SprinSeeder::class,
            PkaSeeder::class,
            AnevDummyTemuanSeeder::class,
        ]);
    }
}
