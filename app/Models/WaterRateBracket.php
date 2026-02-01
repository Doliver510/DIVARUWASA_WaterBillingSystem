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

        // Get brackets ordered by sort_order
        $brackets = self::orderBy('sort_order')->get();

        foreach ($brackets as $bracket) {
            // Determine the effective range for this bracket
            $bracketMin = $bracket->min_cubic;
            $bracketMax = $bracket->max_cubic ?? PHP_INT_MAX;

            // Skip if consumption doesn't reach this bracket
            if ($consumption < $bracketMin) {
                break;
            }

            // Calculate how many cubic meters fall within this bracket
            // Start: max of (bracket min, base coverage + 1)
            // End: min of (consumption, bracket max)
            $effectiveStart = max($bracketMin, $baseCoversCubic + 1);
            $effectiveEnd = min($consumption, $bracketMax);

            // Calculate units in this bracket
            $cubicsInBracket = $effectiveEnd - $effectiveStart + 1;

            if ($cubicsInBracket > 0) {
                $totalCharge += $cubicsInBracket * (float) $bracket->rate_per_cubic;
            }
        }

        return $totalCharge;
    }

    /**
     * Get a breakdown of charges for display on bills/receipts.
     *
     * @param  int  $consumption  Total cubic meters consumed
     * @return array Array with base charge and tiers breakdown
     */
    public static function getChargeBreakdown(int $consumption): array
    {
        $baseCharge = (float) AppSetting::getValue('base_charge', 150);
        $baseCoversCubic = (int) AppSetting::getValue('base_charge_covers_cubic', 10);

        $breakdown = [
            'base_charge' => $baseCharge,
            'base_covers' => $baseCoversCubic,
            'consumption' => $consumption,
            'tiers' => [],
            'total' => $baseCharge,
        ];

        if ($consumption <= $baseCoversCubic) {
            return $breakdown;
        }

        $brackets = self::orderBy('sort_order')->get();

        foreach ($brackets as $bracket) {
            // Determine the effective range for this bracket
            $bracketMin = $bracket->min_cubic;
            $bracketMax = $bracket->max_cubic ?? PHP_INT_MAX;

            // Skip if consumption doesn't reach this bracket
            if ($consumption < $bracketMin) {
                break;
            }

            // Calculate how many cubic meters fall within this bracket
            $effectiveStart = max($bracketMin, $baseCoversCubic + 1);
            $effectiveEnd = min($consumption, $bracketMax);

            // Calculate units in this bracket
            $cubicsInBracket = $effectiveEnd - $effectiveStart + 1;

            if ($cubicsInBracket > 0) {
                $subtotal = $cubicsInBracket * (float) $bracket->rate_per_cubic;
                $maxLabel = $bracket->max_cubic ?? '∞';
                $breakdown['tiers'][] = [
                    'range' => "{$bracket->min_cubic}-{$maxLabel} cubic meters",
                    'units' => $cubicsInBracket,
                    'rate' => (float) $bracket->rate_per_cubic,
                    'amount' => $subtotal,
                ];
                $breakdown['total'] += $subtotal;
            }
        }

        return $breakdown;
    }
}
