<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Seed the default blocks (Block 0 through Block 10).
     */
    public function run(): void
    {
        $blocks = [];

        // Create Block 0 through Block 10
        for ($i = 0; $i <= 10; $i++) {
            $blocks[] = [
                'name' => "Block {$i}",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Block::insert($blocks);
    }
}
