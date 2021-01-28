<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
          'name' => $this->faker->firstName,
          'surname' => $this->faker->lastName . " " . $this->faker->lastName,
          'email' => $this->faker->unique()->safeEmail,
          'password' => Hash::make('password'),
          'user_type_id' => $this->randomUserType(),
          'birth_date' => $this->faker->date($format = 'Y-m-d', $max = '-18 years'),
          'actual_position' => $this->faker->company,
          'phone' => $this->faker->mobileNumber,
          'dni' => $this->faker->dni,
          'address_street' => $this->faker->streetName,
          'address_number' => $this->faker->buildingNumber,
          'address_city' => $this->faker->city,
          'address_cp' => $this->faker->postcode
        ];

    }

    private function randomUserType(){
      $alloweduserTypes = UserType::where('rank','!=',1)->get();
      return $alloweduserTypes->random();
    }


}
