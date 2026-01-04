<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterRateBracket extends Model
{
    protected $fillable = [
        'min_cubic',
        'max_cubic',
        'rate_per_cubic',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_cubic' => 'decimal:2',
        ];
    }

    /**
     * Calculate the total water charge for a given consumption.
     *
     * Billing Logic:
     * 1. Base charge covers the first X cu.m (configured in settings)
     * 2. Excess consumption above X cu.m uses tiered bracket rates
     *
     * Example with base_charge=150, base_charge_covers_cubic=10:
     * - 5 cu.m  → ₱150 (base charge only)
     * - 10 cu.m → ₱150 (base charge only)
     * - 15 cu.m → ₱150 + (5 × rate for 11-20 bracket)
     * - 25 cu.m → ₱150 + (10 × rate for 11-20) + (5 × rate for 21-30)
     *
     * @param  int  $consumption  Total cubic meters consumed
     * @return float Total computed charge
     */
    public static function calculateCharge(int $consumption): float
    {
        // Get base charge settings
        $baseCharge = (float) AppSetting::getValue('base_charge', 150);
        $baseCoversCubic = (int) AppSetting::getValue('base_charge_covers_cubic', 10);

        // Start with base charge (always charged)
        $totalCharge = $baseCharge;

        // If consumption is within base charge coverage, no excess to calculate
        if ($consumption <= $baseCoversCubic) {
            return $totalCharge;
        }

        // Calculate excess consumption
        $excessCubic = $consumption - $baseCoversCubic;

        // Get brackets ordered by sort_order (these are for excess consumption only)
        $brackets = self::orderBy('sort_order')->get();

        foreach ($brackets as $bracket) {
            if ($excessCubic <= 0) {
                break;
            }

            // Calculate how many excess cubic meters fall into this bracket
            // Bracket min_cubic is the actual consumption level (e.g., 11 means 11th cu.m)
            // We need to translate this to excess cubic meters
            $bracketExcessStart = $bracket->min_cubic - $baseCoversCubic - 1; // e.g., 11 - 10 - 1 = 0
            $bracketExcessEnd = $bracket->max_cubic ? $bracket->max_cubic - $baseCoversCubic : PHP_INT_MAX;

            // How many excess cubics are in this bracket range?
            $cubicsInBracket = min($excessCubic, $bracketExcessEnd) - $bracketExcessStart;
            $cubicsInBracket = max(0, $cubicsInBracket);

            if ($cubicsInBracket > 0) {
                $totalCharge += $cubicsInBracket * (float) $bracket->rate_per_cubic;
                $excessCubic -= $cubicsInBracket;
            }
        }

        return $totalCharge;
    }

    /**
     * Get a breakdown of charges for display on bills/receipts.
     *
     * @param  int  $consumption  Total cubic meters consumed
     * @return array Array with base charge and excess breakdown
     */
    public static function getChargeBreakdown(int $consumption): array
    {
        $baseCharge = (float) AppSetting::getValue('base_charge', 150);
        $baseCoversCubic = (int) AppSetting::getValue('base_charge_covers_cubic', 10);

        $breakdown = [
            'base_charge' => $baseCharge,
            'base_covers_cubic' => $baseCoversCubic,
            'consumption' => $consumption,
            'excess_cubic' => max(0, $consumption - $baseCoversCubic),
            'excess_charges' => [],
            'total' => $baseCharge,
        ];

        if ($consumption <= $baseCoversCubic) {
            return $breakdown;
        }

        $excessCubic = $consumption - $baseCoversCubic;
        $brackets = self::orderBy('sort_order')->get();

        foreach ($brackets as $bracket) {
            if ($excessCubic <= 0) {
                break;
            }

            $bracketExcessStart = $bracket->min_cubic - $baseCoversCubic - 1;
            $bracketExcessEnd = $bracket->max_cubic ? $bracket->max_cubic - $baseCoversCubic : PHP_INT_MAX;

            $cubicsInBracket = min($excessCubic, $bracketExcessEnd) - $bracketExcessStart;
            $cubicsInBracket = max(0, $cubicsInBracket);

            if ($cubicsInBracket > 0) {
                $subtotal = $cubicsInBracket * (float) $bracket->rate_per_cubic;
                $breakdown['excess_charges'][] = [
                    'range' => $bracket->min_cubic.'-'.($bracket->max_cubic ?? '∞'),
                    'cubic_meters' => $cubicsInBracket,
                    'rate' => (float) $bracket->rate_per_cubic,
                    'subtotal' => $subtotal,
                ];
                $breakdown['total'] += $subtotal;
                $excessCubic -= $cubicsInBracket;
            }
        }

        return $breakdown;
    }
}
