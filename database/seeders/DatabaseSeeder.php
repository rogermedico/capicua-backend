<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call([
        SummercamptitleSeeder::class,
        UserTypesSeeder::class,
        UserSeeder::class,
        SummerCampTitleUserSeeder::class

      ]);


        // \App\Models\User::factory(10)->create();
    }
}
