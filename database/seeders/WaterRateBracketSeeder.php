<?php

namespace Database\Seeders;

use App\Models\WaterRateBracket;
use Illuminate\Database\Seeder;

class WaterRateBracketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brackets = [
            [
                'min_cubic' => 0,
                'max_cubic' => 10,
                'rate_per_cubic' => 10.00,
                'sort_order' => 1,
            ],
            [
                'min_cubic' => 11,
                'max_cubic' => 20,
                'rate_per_cubic' => 15.00,
                'sort_order' => 2,
            ],
            [
                'min_cubic' => 21,
                'max_cubic' => 30,
                'rate_per_cubic' => 20.00,
                'sort_order' => 3,
            ],
            [
                'min_cubic' => 31,
                'max_cubic' => null, // Unlimited
                'rate_per_cubic' => 25.00,
                'sort_order' => 4,
            ],
        ];

        foreach ($brackets as $bracket) {
            WaterRateBracket::firstOrCreate(
                ['min_cubic' => $bracket['min_cubic']],
                $bracket
            );
        }
    }
}
