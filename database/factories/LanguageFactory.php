<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
      $languageAttributes = $this->randomLanguageAtributes();
        return [
          'name' => $languageAttributes->name,
          'level' => $languageAttributes->level,
          'finish_date' => $languageAttributes->finish_date
          
        ];

    }

    private function randomLanguageAtributes(){

      $languageNames = [
        'Català',
        'Castellà',
        'Anglès',
        'Francès',
      ];

      $languageLevels = [
        'Native',
        'A1',
        'A2',
        'B1',
        'B2',
        'C1',
        'C2'
      ];

      $languageFinishDate = ((bool)rand(0,1)?$this->faker->date($format = 'Y', $startDate = '-6 years', $max='now').'-01-01':null);

        return (object)[
          'name' => $languageNames[array_rand($languageNames,1)],
          'level' => $languageLevels[array_rand($languageLevels,1)],
          'finish_date' => $languageFinishDate
        ];

    }


}
