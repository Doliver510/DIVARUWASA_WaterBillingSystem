<?php

namespace Database\Seeders;

use App\Models\Consumer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AppSettingSeeder::class,
            WaterRateBracketSeeder::class,
        ]);

        // Create an Admin User
        $adminRole = Role::where('slug', 'admin')->first();
        User::factory()->create([
            'first_name' => 'Admin',
            'middle_name' => null,
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        // Create a sample Consumer
        $consumerRole = Role::where('slug', 'consumer')->first();
        $consumerUser = User::factory()->create([
            'first_name' => 'Juan',
            'middle_name' => 'Reyes',
            'last_name' => 'Santos',
            'email' => 'consumer@example.com',
            'role_id' => $consumerRole->id,
        ]);

        Consumer::create([
            'user_id' => $consumerUser->id,
            'id_no' => '001',
            'address' => 'Purok 1, Barangay Sample, Municipality, Province',
            'status' => 'active',
        ]);
    }
}
