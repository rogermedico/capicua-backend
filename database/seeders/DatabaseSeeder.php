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
            CourseSeeder::class,
            UserTypesSeeder::class,
            UserSeeder::class,
            CourseUserSeeder::class,
            DrivingLicenceSeeder::class,
            EducationSeeder::class,
            LanguageSeeder::class,
            HomePostSeeder::class
        ]);
    }
}
