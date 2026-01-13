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
        'or_number',
        'bill_id',
        'consumer_id',
        'processed_by',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'remarks',
        'paid_at',
    ];

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
     * Get the bill this payment belongs to.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
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
     * Generate the next Official Receipt number.
     * Format: OR-YYYYMMDD-XXXX (e.g., OR-20260111-0001)
     */
    public static function generateOrNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'OR-'.$today.'-';

        // Get the last OR number for today
        $lastPayment = self::where('or_number', 'like', $prefix.'%')
            ->orderByDesc('or_number')
            ->first();

        if ($lastPayment) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastPayment->or_number, -4);
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
