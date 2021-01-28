<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\DriverLicence;
use App\Models\User;

class DriverLicenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      User::all()->each(function ($user) use (&$driverLicences){

        if((bool) random_int(0, 1)){
          $driverLicence = DriverLicence::create([
            'user_id' => $user['id'],
            'type'=> 'A'
            ]);
        }
        if((bool) random_int(0, 1)){
          $driverLicence = DriverLicence::create([
            'user_id' => $user['id'],
            'type'=> 'B'
            ]);
        }

    });
    }
}
