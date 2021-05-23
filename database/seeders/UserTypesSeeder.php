<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userTypes = [
            ['rank' => 1, 'name' => 'admin'],
            ['rank' => 2, 'name' => 'moderator'],
            ['rank' => 3, 'name' => 'worker']
        ];

        foreach($userTypes as $userType){
            UserType::create($userType);
        }
    }
}
