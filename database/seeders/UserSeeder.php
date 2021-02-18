<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      User::factory()->create([
        'email' => 'admin@gmail.com',
        'user_type_id' => 1,
        'email_verified_at' => Carbon::now()
      ]);
      User::factory()->create([
        'email' => 'moderator@gmail.com',
        'user_type_id' => 2,
        'email_verified_at' => Carbon::now()
      ]);
      User::factory()->create([
        'email' => 'worker@gmail.com',
        'user_type_id' => 3,
        'email_verified_at' => Carbon::now()
      ]);
      User::factory()->times(50)->create();

    }
}
