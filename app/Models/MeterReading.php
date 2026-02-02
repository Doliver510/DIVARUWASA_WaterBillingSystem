<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'consumer_id',
        'reading_value',
        'previous_reading',
        'consumption',
        'reading_date',
        'billing_period',
        'read_by',
        'is_billed',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'reading_value' => 'integer',
            'previous_reading' => 'integer',
            'consumption' => 'integer',
            'reading_date' => 'date',
            'is_billed' => 'boolean',
        ];
    }

    /**
     * Get the consumer this reading belongs to.
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * Get the user who recorded this reading.
     */
    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    /**
     * Check if the reading can be edited.
     */
    public function canBeEdited(): bool
    {
        return ! $this->is_billed;
    }

    /**
     * Get the previous reading for a consumer.
     */
    public static function getPreviousReading(int $consumerId): int
    {
        $lastReading = self::where('consumer_id', $consumerId)
            ->orderByDesc('billing_period')
            ->first();

        return $lastReading ? $lastReading->reading_value : 0;
    }

    /**
     * Get the current billing period (format: YYYY-MM).
     * 
     * The billing period is named by the END month of the period.
     * Example: Period from Jan 10 to Feb 10 = "2026-02" (February 2026)
     * This matches the paper bill convention used by DIVARUWASA.
     */
    public static function getCurrentBillingPeriod(): string
    {
        $startDay = (int) AppSetting::getValue('billing_cycle_start_day', 10);
        $today = now();

        // Period is named by the END month
        // If we're before the start day, we're still in the current month's billing period
        // (period started last month, ends this month)
        // If we're at or past the start day, we're in next month's billing period
        // (period started this month, ends next month)
        if ($today->day < $startDay) {
            return $today->format('Y-m'); // Current month (period ends this month)
        }

        return $today->addMonth()->format('Y-m'); // Next month (period ends next month)
    }

    /**
     * Format billing period for display (e.g., "January 2026").
     */
    public function getBillingPeriodLabelAttribute(): string
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->billing_period);

        return $date->format('F Y');
    }

    /**
     * Calculate consumption before saving.
     */
    protected static function booted(): void
    {
        static::creating(function (MeterReading $reading) {
            $reading->consumption = max(0, $reading->reading_value - $reading->previous_reading);
        });

        static::updating(function (MeterReading $reading) {
            $reading->consumption = max(0, $reading->reading_value - $reading->previous_reading);
        });
    }
}
