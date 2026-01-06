<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            [
                'name' => 'PVC Pipe 1/2"',
                'description' => 'Half-inch PVC pipe for water lines',
                'unit' => 'meters',
                'unit_price' => 35.00,
                'stock_quantity' => 100,
                'reorder_level' => 20,
            ],
            [
                'name' => 'PVC Pipe 3/4"',
                'description' => 'Three-quarter inch PVC pipe',
                'unit' => 'meters',
                'unit_price' => 45.00,
                'stock_quantity' => 80,
                'reorder_level' => 15,
            ],
            [
                'name' => 'PVC Elbow 1/2"',
                'description' => '90-degree elbow fitting',
                'unit' => 'pcs',
                'unit_price' => 15.00,
                'stock_quantity' => 50,
                'reorder_level' => 10,
            ],
            [
                'name' => 'PVC Elbow 3/4"',
                'description' => '90-degree elbow fitting',
                'unit' => 'pcs',
                'unit_price' => 20.00,
                'stock_quantity' => 40,
                'reorder_level' => 10,
            ],
            [
                'name' => 'PVC Tee 1/2"',
                'description' => 'T-junction fitting',
                'unit' => 'pcs',
                'unit_price' => 18.00,
                'stock_quantity' => 30,
                'reorder_level' => 10,
            ],
            [
                'name' => 'Gate Valve 1/2"',
                'description' => 'Brass gate valve',
                'unit' => 'pcs',
                'unit_price' => 150.00,
                'stock_quantity' => 15,
                'reorder_level' => 5,
            ],
            [
                'name' => 'Water Meter',
                'description' => 'Standard residential water meter',
                'unit' => 'pcs',
                'unit_price' => 850.00,
                'stock_quantity' => 10,
                'reorder_level' => 3,
            ],
            [
                'name' => 'Teflon Tape',
                'description' => 'Thread seal tape for pipe connections',
                'unit' => 'rolls',
                'unit_price' => 25.00,
                'stock_quantity' => 50,
                'reorder_level' => 15,
            ],
            [
                'name' => 'PVC Solvent Cement',
                'description' => 'Adhesive for PVC pipe joints',
                'unit' => 'cans',
                'unit_price' => 120.00,
                'stock_quantity' => 20,
                'reorder_level' => 5,
            ],
            [
                'name' => 'Hose Clamp 1/2"',
                'description' => 'Stainless steel hose clamp',
                'unit' => 'pcs',
                'unit_price' => 12.00,
                'stock_quantity' => 100,
                'reorder_level' => 20,
            ],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(['name' => $material['name']], $material);
        }
    }
}
