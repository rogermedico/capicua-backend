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
      $user = [ 
        'name' => 'admin',
        'surname' => 'admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password'),
        'user_type_id' => 1

      ];

      User::create($user);

      User::factory()->times(50)->create();

    }
}
