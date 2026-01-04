<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove deprecated setting
        AppSetting::where('key', 'water_rate_per_cu_m')->delete();

        $settings = [
            [
                'key' => 'minimum_bill_amount',
                'value' => '150.00',
                'description' => 'Minimum bill amount even if consumption is low',
                'type' => 'currency',
            ],
            [
                'key' => 'penalty_fee',
                'value' => '50.00',
                'description' => 'Fixed penalty for overdue payments',
                'type' => 'currency',
            ],
            [
                'key' => 'registration_fee',
                'value' => '500.00',
                'description' => 'Fee for new connection application',
                'type' => 'currency',
            ],
            [
                'key' => 'payment_due_days',
                'value' => '15',
                'description' => 'Number of days before payment is due',
                'type' => 'number',
            ],
            [
                'key' => 'reconnection_fee',
                'value' => '200.00',
                'description' => 'Fee for reconnecting service',
                'type' => 'currency',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
