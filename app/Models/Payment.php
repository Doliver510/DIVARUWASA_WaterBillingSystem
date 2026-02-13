<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'payment_type',
        'bill_id',
        'maintenance_request_id',
        'consumer_id',
        'processed_by',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'remarks',
        'paid_at',
    ];

    /**
     * Payment type constants.
     */
    public const TYPE_BILL = 'bill';

    public const TYPE_MAINTENANCE = 'maintenance';

    public const TYPE_METER = 'meter';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the bill this payment belongs to (for bill payments).
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the maintenance request (for maintenance payments).
     */
    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    /**
     * Get the consumer this payment belongs to.
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * Get the user who processed this payment.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Check if this is a bill payment.
     */
    public function isBillPayment(): bool
    {
        return $this->payment_type === self::TYPE_BILL;
    }

    /**
     * Check if this is a maintenance payment.
     */
    public function isMaintenancePayment(): bool
    {
        return $this->payment_type === self::TYPE_MAINTENANCE;
    }

    /**
     * Check if this is a meter payment (early payoff).
     */
    public function isMeterPayment(): bool
    {
        return $this->payment_type === self::TYPE_METER;
    }

    /**
     * Get a description of what this payment is for.
     */
    public function getPaymentDescriptionAttribute(): string
    {
        if ($this->isBillPayment() && $this->bill) {
            return 'Water Bill - '.$this->bill->billing_period_label;
        }

        if ($this->isMaintenancePayment() && $this->maintenanceRequest) {
            return 'Maintenance Materials - Request #'.$this->maintenanceRequest->id;
        }

        if ($this->isMeterPayment()) {
            return 'Meter Payment';
        }

        return 'Payment';
    }

    /**
     * Generate the next Receipt number.
     * Format: RCT-YYYYMMDD-XXXX (e.g., RCT-20260111-0001)
     */
    public static function generateReceiptNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'RCT-'.$today.'-';

        // Get the last receipt number for today
        $lastPayment = self::where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('receipt_number')
            ->first();

        if ($lastPayment) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastPayment->receipt_number, -4);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for payments by a specific processor (cashier/admin).
     */
    public function scopeByProcessor($query, int $userId)
    {
        return $query->where('processed_by', $userId);
    }

    /**
     * Scope for payments on a specific date.
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('paid_at', $date);
    }

    /**
     * Scope for today's payments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('paid_at', now()->toDateString());
    }
}
