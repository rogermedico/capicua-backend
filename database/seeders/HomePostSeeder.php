<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomePost;

class HomePostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      HomePost::factory()->times(5)->create();
    }
}
