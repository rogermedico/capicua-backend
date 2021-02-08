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

        $languageNames = [
          'Català',
          'Castellà',
          'Anglès',
          'Francès',
        ];

        $nLanguages = random_int(0,3);
        if($nLanguages){
          $languageIndexes = (array) array_rand($languageNames,$nLanguages);
          foreach($languageIndexes as $i){
            Language::factory()->create([
              'user_id'=> $user->id,
              'name' => $languageNames[$i]
            ]);
          }
        }

      });
    }
}
