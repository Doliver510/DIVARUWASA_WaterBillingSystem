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
        // Remove deprecated/unused settings
        $deprecatedKeys = [
            'water_rate_per_cu_m',
            'minimum_bill_amount',
            'payment_due_days',
            'registration_fee',
            'reconnection_fee',
            'association_name',
            'association_full_name',
            'association_address',
            'association_tin',
            'association_email',
        ];

        AppSetting::whereIn('key', $deprecatedKeys)->delete();

        // Core billing settings only
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
                'description' => 'Fixed penalty for late payments',
                'type' => 'currency',
            ],
            [
                'key' => 'grace_period_days',
                'value' => '5',
                'description' => 'Days after due date before penalty applies',
                'type' => 'number',
            ],
            [
                'key' => 'billing_cycle_start_day',
                'value' => '10',
                'description' => 'Day of month when billing cycle starts',
                'type' => 'number',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
