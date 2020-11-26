<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SummerCampTitle;

class SummerCampTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $summerCampTitles = [
        ['name' => 'Monitor de lleure'],
        ['name' => 'Director de lleure']
      ];

      foreach($summerCampTitles as $summerCampTitle){
        SummerCampTitle::create($summerCampTitle);
      }
    }
}
