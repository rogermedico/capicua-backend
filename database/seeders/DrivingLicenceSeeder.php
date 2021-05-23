<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\DrivingLicence;
use App\Models\User;

class DrivingLicenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::all()->each(function ($user) {

            if((bool) random_int(0, 1)){
              $driverLicence = DrivingLicence::create([
                'user_id' => $user['id'],
                'type'=> 'A'
                ]);
            }
            
            if((bool) random_int(0, 1)){
              $driverLicence = DrivingLicence::create([
                'user_id' => $user['id'],
                'type'=> 'B'
                ]);
            }

        });
    }
}
