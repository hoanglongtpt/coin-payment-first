<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VipCard;

class VipCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        VipCard::insert([
            [
                'amount_usd' => 19,
                'ticket_count' => 29,
                'description' => 'Daily for 142 Days 7'
            ],
            [
                'amount_usd' => 49,
                'ticket_count' => 49,
                'description' => 'Daily for 272 Days 7'
            ],
            [
                'amount_usd' => 69,
                'ticket_count' => 69,
                'description' => 'Daily for 143 Days 7'
            ],
            [
                'amount_usd' => 119,
                'ticket_count' => 119,
                'description' => 'Daily for 273 Days 7'
            ],
        ]);
    }
}
