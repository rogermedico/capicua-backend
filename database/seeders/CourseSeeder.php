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
		    ['name' => 'Manipulació d\'aliments']
      ];

      foreach($coursesNames as $coursesName){
        Course::create($coursesName);
      }
    }
}
