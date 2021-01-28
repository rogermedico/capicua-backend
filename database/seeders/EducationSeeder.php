<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Education;
use App\Models\User;

class EducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      User::all()->each(function ($user){

        $nEducations = random_int(0,3);
        for($i=0;$i<$nEducations;$i++){
          Education::factory()->create(['user_id'=> $user->id]);
        }

      });
    }
}
