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
        // Remove deprecated settings
        AppSetting::where('key', 'water_rate_per_cu_m')->delete();
        AppSetting::where('key', 'minimum_bill_amount')->delete();

        $settings = [
            [
                'key' => 'base_charge',
                'value' => '150.00',
                'description' => 'Minimum charge that covers the first 10 cu.m of consumption',
                'type' => 'currency',
            ],
            [
                'key' => 'base_charge_covers_cubic',
                'value' => '10',
                'description' => 'Number of cubic meters covered by the minimum charge',
                'type' => 'number',
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
            AppSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
