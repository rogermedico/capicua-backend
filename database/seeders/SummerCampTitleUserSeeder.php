<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SummerCampTitle;
use App\Models\User;

class SummerCampTitleUserSeeder extends Seeder
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
        
        $titles = SummerCampTitle::all()->random(rand(0,SummerCampTitle::all()->count()));
        
        for($i=0;$i<count($titles);$i++){
          
          //random unique number
          $number = rand(10000,99999);
          while(in_array($number,$numbers)) $number = rand(10000,99999);
          array_push($numbers,$number);

          $user->summerCampTitles()->attach($titles[$i],['number'=> $number]);
        }

    });
    }
}
