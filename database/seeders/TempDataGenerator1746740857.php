<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TempDataGenerator1746740857 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Seeding table: holidays
        for ($i = 0; $i < 5; $i++) {
            DB::table('holidays')->insert([
                'title' => $faker->sentence(),
                'description' => $faker->text(100),
                'date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'is_recurring' => $faker->word(),
                'is_active' => $faker->word()
            ]);
        }

    }
}
