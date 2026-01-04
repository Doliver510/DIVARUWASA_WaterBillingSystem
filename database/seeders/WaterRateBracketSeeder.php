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
     * The first 10 cu.m is covered by the base_charge setting.
     * These rates apply from 11 cu.m onwards.
     */
    public function run(): void
    {
        // Remove old 0-10 bracket if exists (now covered by base_charge)
        WaterRateBracket::where('min_cubic', 0)->delete();

        $brackets = [
            [
                'min_cubic' => 11,
                'max_cubic' => 20,
                'rate_per_cubic' => 15.00,
                'sort_order' => 1,
            ],
            [
                'min_cubic' => 21,
                'max_cubic' => 30,
                'rate_per_cubic' => 20.00,
                'sort_order' => 2,
            ],
            [
                'min_cubic' => 31,
                'max_cubic' => null, // Unlimited
                'rate_per_cubic' => 25.00,
                'sort_order' => 3,
            ],
        ];

        foreach ($brackets as $bracket) {
            WaterRateBracket::updateOrCreate(
                ['min_cubic' => $bracket['min_cubic']],
                $bracket
            );
        }
    }
}
