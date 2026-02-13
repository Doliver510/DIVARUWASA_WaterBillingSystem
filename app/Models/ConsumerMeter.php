<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumerMeter extends Model
{
    protected $fillable = [
        'consumer_id',
        'meter_number',
        'installed_at',
        'removed_at',
        'removal_reason',
        'maintenance_request_id',
        'meter_cost',
        'installment_months',
        'installments_billed',
        'installment_amount',
        'total_paid',
        'fully_paid',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'removed_at' => 'date',
            'meter_cost' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'fully_paid' => 'boolean',
        ];
    }

    /**
     * Get the consumer this meter belongs to.
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * Get the maintenance request that triggered this meter installation.
     */
    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    /**
     * Get the remaining balance owed for this meter.
     */
    public function getRemainingBalance(): float
    {
        if ($this->fully_paid || ! $this->meter_cost) {
            return 0;
        }

        return max(0, (float) $this->meter_cost - (float) $this->total_paid);
    }

    /**
     * Get remaining installments count.
     */
    public function getRemainingInstallments(): int
    {
        if ($this->fully_paid) {
            return 0;
        }

        return max(0, $this->installment_months - $this->installments_billed);
    }

    /**
     * Mark one installment as billed (called when a bill is generated).
     */
    public function markInstallmentBilled(float $amount): void
    {
        $this->installments_billed += 1;
        $this->total_paid += $amount;

        if ($this->installments_billed >= $this->installment_months || $this->getRemainingBalance() <= 0) {
            $this->fully_paid = true;
        }

        $this->save();
    }

    /**
     * Mark meter as fully paid (early payoff).
     */
    public function markFullyPaid(float $amount): void
    {
        $this->total_paid += $amount;
        $this->fully_paid = true;
        $this->save();
    }

    /**
     * Check if this meter has pending installments.
     */
    public function hasPendingInstallments(): bool
    {
        return ! $this->fully_paid
            && $this->meter_cost > 0
            && $this->installments_billed < $this->installment_months;
    }
}
