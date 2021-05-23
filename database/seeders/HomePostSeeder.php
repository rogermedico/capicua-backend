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
        for($i=0;$i<5;$i++){
            $homePost = HomePost::factory()->create([
              'position' => $i+1
            ]);
        }
    }
}
