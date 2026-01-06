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
            // Billing Settings
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
                'description' => 'Fixed penalty for late payments (during grace period)',
                'type' => 'currency',
            ],
            [
                'key' => 'grace_period_days',
                'value' => '5',
                'description' => 'Grace period days after disconnection date (penalty applies)',
                'type' => 'number',
            ],
            [
                'key' => 'registration_fee',
                'value' => '500.00',
                'description' => 'Fee for new connection application',
                'type' => 'currency',
            ],
            [
                'key' => 'reconnection_fee',
                'value' => '200.00',
                'description' => 'Fee for reconnecting service',
                'type' => 'currency',
            ],
            [
                'key' => 'billing_cycle_start_day',
                'value' => '10',
                'description' => 'Day of month when billing cycle starts (e.g., 10 means Nov 10 - Dec 10)',
                'type' => 'number',
            ],

            // Association Information
            [
                'key' => 'association_name',
                'value' => 'DIVARUWASA',
                'description' => 'Association short name',
                'type' => 'text',
            ],
            [
                'key' => 'association_full_name',
                'value' => 'Diamond Valley Rural Waterworks and Sanitation Association, INC.',
                'description' => 'Full legal name of the association',
                'type' => 'text',
            ],
            [
                'key' => 'association_address',
                'value' => 'Block 2, Diamond Valley, Tambler, 9500, General Santos City (Dadiangas), South Cotabato Philippines',
                'description' => 'Association address',
                'type' => 'text',
            ],
            [
                'key' => 'association_tin',
                'value' => '625-217-805-000',
                'description' => 'Tax Identification Number (Non-Vat Reg)',
                'type' => 'text',
            ],
            [
                'key' => 'association_email',
                'value' => 'Divaruwasa.tambler@gmail.com',
                'description' => 'Association email address',
                'type' => 'text',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // Remove payment_due_days as we now use grace_period_days
        AppSetting::where('key', 'payment_due_days')->delete();
    }
}
