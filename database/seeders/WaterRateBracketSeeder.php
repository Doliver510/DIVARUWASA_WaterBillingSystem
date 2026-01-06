<?php

namespace Database\Seeders;

use App\Models\WaterRateBracket;
use Illuminate\Database\Seeder;

class WaterRateBracketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: These brackets are for EXCESS consumption only.
     * The first 10 cu.m is covered by the base_charge setting (₱150).
     * These rates apply from 11 cu.m onwards.
     *
     * Rates based on actual DIVARUWASA billing:
     * - 11-20 cu.m: ₱12/cu.m
     * - 21-30 cu.m: ₱15/cu.m
     * - 31+ cu.m: ₱17/cu.m
     */
    public function run(): void
    {
        // Remove old brackets and recreate with correct rates
        WaterRateBracket::truncate();

        $brackets = [
            [
                'min_cubic' => 11,
                'max_cubic' => 20,
                'rate_per_cubic' => 12.00,
                'sort_order' => 1,
            ],
            [
                'min_cubic' => 21,
                'max_cubic' => 30,
                'rate_per_cubic' => 15.00,
                'sort_order' => 2,
            ],
            [
                'min_cubic' => 31,
                'max_cubic' => null, // Unlimited
                'rate_per_cubic' => 17.00,
                'sort_order' => 3,
            ],
        ];

        foreach ($brackets as $bracket) {
            WaterRateBracket::create($bracket);
        }
    }
}
