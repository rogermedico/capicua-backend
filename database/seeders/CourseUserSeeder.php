<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $numbers = [];
      User::all()->each(function ($user) use (&$numbers){
        
        $courses = Course::all()->random(rand(0,Course::all()->count()));
        
        for($i=0;$i<count($courses);$i++){
          
          //random unique number
          $number = rand(10000,99999);
          while(in_array($number,$numbers)) $number = rand(10000,99999);
          array_push($numbers,$number);

          $user->courses()->attach($courses[$i],['number'=> $number]);
        }

    });
    }
}
