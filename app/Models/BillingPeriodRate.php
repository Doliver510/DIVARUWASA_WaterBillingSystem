<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPeriodRate extends Model
{
    protected $fillable = [
        'billing_period',
        'base_charge',
        'base_charge_covers_cubic',
        'penalty_fee',
        'grace_period_days',
        'rate_brackets',
        'locked_by',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'base_charge' => 'decimal:2',
            'penalty_fee' => 'decimal:2',
            'rate_brackets' => 'array',
            'locked_at' => 'datetime',
        ];
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the current billing period string (e.g., "2026-02").
     * 
     * The billing period is named by the END month of the period.
     * Example: Period from Jan 10 to Feb 10 = "2026-02" (February 2026)
     * This matches the paper bill convention used by DIVARUWASA.
     */
    public static function getCurrentPeriod(): string
    {
        $cycleStartDay = (int) AppSetting::getValue('billing_cycle_start_day', 10);
        $now = now();

        // If we're before the cycle start day, we're still in the current month's period
        // (period started last month, ends this month)
        if ($now->day < $cycleStartDay) {
            return $now->format('Y-m');
        }

        // Otherwise, we're in the next month's period
        // (period started this month, ends next month)
        return $now->addMonth()->format('Y-m');
    }

    /**
     * Check if a billing period is locked (has rates snapshotted).
     */
    public static function isLocked(string $billingPeriod): bool
    {
        return self::where('billing_period', $billingPeriod)->exists();
    }

    /**
     * Lock rates for a billing period by creating a snapshot.
     */
    public static function lockPeriod(string $billingPeriod, ?int $userId = null): self
    {
        // Get current rates from settings
        $baseCharge = (float) AppSetting::getValue('base_charge', 150);
        $baseCoversCubic = (int) AppSetting::getValue('base_charge_covers_cubic', 10);
        $penaltyFee = (float) AppSetting::getValue('penalty_fee', 50);
        $gracePeriodDays = (int) AppSetting::getValue('grace_period_days', 5);

        // Get current rate brackets
        $brackets = WaterRateBracket::orderBy('sort_order')
            ->get(['min_cubic', 'max_cubic', 'rate_per_cubic', 'sort_order'])
            ->toArray();

        return self::create([
            'billing_period' => $billingPeriod,
            'base_charge' => $baseCharge,
            'base_charge_covers_cubic' => $baseCoversCubic,
            'penalty_fee' => $penaltyFee,
            'grace_period_days' => $gracePeriodDays,
            'rate_brackets' => $brackets,
            'locked_by' => $userId ?? auth()->id(),
            'locked_at' => now(),
        ]);
    }

    /**
     * Get rates for a billing period (locked or current).
     */
    public static function getRatesForPeriod(string $billingPeriod): array
    {
        $locked = self::where('billing_period', $billingPeriod)->first();

        if ($locked) {
            return [
                'base_charge' => (float) $locked->base_charge,
                'base_charge_covers_cubic' => (int) $locked->base_charge_covers_cubic,
                'penalty_fee' => (float) $locked->penalty_fee,
                'grace_period_days' => (int) $locked->grace_period_days,
                'rate_brackets' => $locked->rate_brackets,
                'is_locked' => true,
                'locked_at' => $locked->locked_at,
            ];
        }

        // Return current settings if period not locked
        return [
            'base_charge' => (float) AppSetting::getValue('base_charge', 150),
            'base_charge_covers_cubic' => (int) AppSetting::getValue('base_charge_covers_cubic', 10),
            'penalty_fee' => (float) AppSetting::getValue('penalty_fee', 50),
            'grace_period_days' => (int) AppSetting::getValue('grace_period_days', 5),
            'rate_brackets' => WaterRateBracket::orderBy('sort_order')
                ->get(['min_cubic', 'max_cubic', 'rate_per_cubic', 'sort_order'])
                ->toArray(),
            'is_locked' => false,
            'locked_at' => null,
        ];
    }

    /**
     * Calculate water charge using period-locked rates.
     */
    public static function calculateChargeForPeriod(int $consumption, string $billingPeriod): float
    {
        $rates = self::getRatesForPeriod($billingPeriod);

        $baseCharge = $rates['base_charge'];
        $baseCoversCubic = $rates['base_charge_covers_cubic'];
        $totalCharge = $baseCharge;

        if ($consumption <= $baseCoversCubic) {
            return $totalCharge;
        }

        foreach ($rates['rate_brackets'] as $bracket) {
            $bracketMin = $bracket['min_cubic'];
            $bracketMax = $bracket['max_cubic'] ?? PHP_INT_MAX;

            if ($consumption < $bracketMin) {
                break;
            }

            $effectiveStart = max($bracketMin, $baseCoversCubic + 1);
            $effectiveEnd = min($consumption, $bracketMax);
            $cubicsInBracket = $effectiveEnd - $effectiveStart + 1;

            if ($cubicsInBracket > 0) {
                $totalCharge += $cubicsInBracket * (float) $bracket['rate_per_cubic'];
            }
        }

        return $totalCharge;
    }
}
