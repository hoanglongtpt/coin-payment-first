<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('packages')->insert([
            ['name' => '5 USD', 'price' => 5.00, 'reward_points' => 19, 'bonus' => 10],
            ['name' => '10 USD', 'price' => 10.00, 'reward_points' => 47, 'bonus' => 10],
            ['name' => '15 USD', 'price' => 15.00, 'reward_points' => 83, 'bonus' => 10],
            ['name' => '20 USD', 'price' => 20.00, 'reward_points' => 128, 'bonus' => 10],
            ['name' => '30 USD', 'price' => 30.00, 'reward_points' => 239, 'bonus' => 10],
            ['name' => '50 USD', 'price' => 50.00, 'reward_points' => 485, 'bonus' => 10],
            ['name' => '100 USD', 'price' => 100.00, 'reward_points' => 1160, 'bonus' => 10],
            ['name' => '150 USD', 'price' => 150.00, 'reward_points' => 1913, 'bonus' => 10],
            ['name' => '199 USD', 'price' => 199.00, 'reward_points' => 2982, 'bonus' => 10],
        ]);
    }
}
