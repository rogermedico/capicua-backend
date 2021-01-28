<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EducationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Education::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
      $randFinishDateFinished = $this->randFinishDateFinished();
        return [
          'user_id' => 2,
          'name' => $this->faker->sentence(3),
          'finish_date' => $randFinishDateFinished->finish_date,
          'finished' => $randFinishDateFinished->finished
        ];

    }

    private function randFinishDateFinished(){
      if((bool)rand(0,1)){
        return (object)[
          'finish_date' => $this->faker->date($format = 'Y-m-d', $max = '-6 years'),
          'finished' => true
        ];
      }
      else {
        return (object)[
          'finish_date' => null,
          'finished' => (bool)rand(0,1)
        ];
      }
    }


}
