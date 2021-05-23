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
        /* three known users one for each role */
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

        /* fifty more random users */
        User::factory()->times(50)->create();

    }
}
