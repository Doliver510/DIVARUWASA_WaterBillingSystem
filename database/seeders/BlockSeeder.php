<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Seed the default blocks (0-10).
     */
    public function run(): void
    {
        $blocks = [];

        // Create blocks 0 through 10
        for ($i = 0; $i <= 10; $i++) {
            $blocks[] = [
                'block_number' => $i,
                'name' => Block::generateName($i),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Block::insert($blocks);
    }
}
