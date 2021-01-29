<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\User;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      User::all()->each(function ($user){

        $nLanguages = random_int(0,3);
        for($i=0;$i<$nLanguages;$i++){
          Language::factory()->create(['user_id'=> $user->id]);
        }

      });
    }
}
