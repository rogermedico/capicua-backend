<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $admin = [ 
        'name' => 'admin',
        'surname' => 'admin',
        'email' => 'admin@gmail.com',
        'password' => Hash::make('password'),
        'user_type_id' => 1
      ];
	  
	$moderator = [ 
        'name' => 'moderator',
        'surname' => 'moderator',
        'email' => 'moderator@gmail.com',
        'password' => Hash::make('password'),
        'user_type_id' => 2
      ];
	  
	$worker = [ 
        'name' => 'worker',
        'surname' => 'worker',
        'email' => 'worker@gmail.com',
        'password' => Hash::make('password'),
        'user_type_id' => 3
      ];
	  
	  

      User::create($admin);
	  User::create($moderator);
	  User::create($worker);
      User::factory()->times(50)->create();

    }
}
