<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with full access',
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'description' => 'Processes payments and generates receipts',
            ],
            [
                'name' => 'Meter Reader',
                'slug' => 'meter-reader',
                'description' => 'Encodes manual meter readings',
            ],
            [
                'name' => 'Maintenance Staff',
                'slug' => 'maintenance-staff',
                'description' => 'Updates maintenance request status',
            ],
            [
                'name' => 'Consumer',
                'slug' => 'consumer',
                'description' => 'Water service consumer',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
