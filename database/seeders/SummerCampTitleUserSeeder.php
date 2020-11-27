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
      
      User::all()->each(function ($user,$summerCampTitles){
        $summerCampTitles = SummerCampTitle::all();
        $user->summerCampTitles()->attach(
            $summerCampTitles->random(rand(0,$summerCampTitles->count()))->pluck('id')->toArray()
        );
    });
    }
}
