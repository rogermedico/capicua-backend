<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coursesNames = [
            ['name' => 'Monitor de lleure'],
            ['name' => 'Director de lleure'],
            ['name' => 'Director de lleure (CFGS Animació Sociocultural)'],
            ['name' => 'Monitor de menjador'],
            ['name' => 'Manipulació d\'aliments'],
            ['name' => 'Vetllador'],
        ];

        foreach($coursesNames as $coursesName){
            Course::create($coursesName);
        }
    }
}
