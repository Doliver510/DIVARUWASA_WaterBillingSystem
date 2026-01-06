<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'consumer_id',
        'meter_reading_id',
        'billing_period',
        'period_from',
        'period_to',
        'previous_reading',
        'present_reading',
        'consumption',
        'water_charge',
        'arrears',
        'penalty',
        'other_charges',
        'total_amount',
        'amount_paid',
        'balance',
        'disconnection_date',
        'due_date_start',
        'due_date_end',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'disconnection_date' => 'date',
            'due_date_start' => 'date',
            'due_date_end' => 'date',
            'water_charge' => 'decimal:2',
            'arrears' => 'decimal:2',
            'penalty' => 'decimal:2',
            'other_charges' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Status labels for display.
     */
    public const STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
    ];

    /**
     * Get the consumer this bill belongs to.
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * Get the meter reading for this bill.
     */
    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    /**
     * Get the payments for this bill.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'unpaid' => 'yellow',
            'partial' => 'orange',
            'paid' => 'green',
            'overdue' => 'red',
            default => 'secondary',
        };
    }

    /**
     * Get formatted billing period label (e.g., "December 2025").
     */
    public function getBillingPeriodLabelAttribute(): string
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->billing_period);

        return $date->format('F Y');
    }

    /**
     * Get formatted due date range (e.g., "Jan 01-05, 2026").
     */
    public function getDueDateRangeAttribute(): string
    {
        $start = $this->due_date_start;
        $end = $this->due_date_end;

        if ($start->month === $end->month) {
            return $start->format('M d').'-'.$end->format('d, Y');
        }

        return $start->format('M d').' - '.$end->format('M d, Y');
    }

    /**
     * Check if bill is in grace period (penalty applies).
     */
    public function isInGracePeriod(): bool
    {
        $today = now()->startOfDay();

        return $today->gt($this->disconnection_date) && $today->lte($this->due_date_end);
    }

    /**
     * Check if bill is overdue (past grace period).
     */
    public function isOverdue(): bool
    {
        return now()->startOfDay()->gt($this->due_date_end) && $this->balance > 0;
    }

    /**
     * Record a payment.
     */
    public function recordPayment(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->balance = max(0, $this->total_amount - $this->amount_paid);

        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }

    /**
     * Apply penalty to the bill.
     */
    public function applyPenalty(): void
    {
        if ($this->penalty > 0) {
            return; // Penalty already applied
        }

        $penaltyFee = (float) AppSetting::getValue('penalty_fee', 50);
        $this->penalty = $penaltyFee;
        $this->total_amount += $penaltyFee;
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->save();
    }

    /**
     * Update status based on current date and payment.
     */
    public function updateStatus(): void
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
    }

    /**
     * Get the previous unpaid balance (arrears) for a consumer.
     */
    public static function getArrears(int $consumerId, ?string $excludePeriod = null): float
    {
        $query = self::where('consumer_id', $consumerId)
            ->where('balance', '>', 0);

        if ($excludePeriod) {
            $query->where('billing_period', '!=', $excludePeriod);
        }

        return (float) $query->sum('balance');
    }
}
