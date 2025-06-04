<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TempDataGenerator1746740542 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Seeding table: maintenance_areas
        for ($i = 0; $i < 5; $i++) {
            DB::table('maintenance_areas')->insert([
                'name' => $faker->name(),
                'description' => $faker->text(100),
                'deleted_at' => $faker->word()
            ]);
        }

    }
}
