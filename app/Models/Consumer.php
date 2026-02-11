<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consumer extends Model
{
    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_CUT_OFF = 'cut_off';
    const STATUS_PULLED_OUT = 'pulled_out';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISCONNECTED => 'Disconnected',
        self::STATUS_CUT_OFF => 'Cut Off',
        self::STATUS_PULLED_OUT => 'Pulled Out',
    ];

    protected $fillable = [
        'user_id',
        'id_no',
        'block_id',
        'lot_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'lot_number' => 'integer',
        ];
    }

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get the badge color class for the status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_DISCONNECTED => 'warning',
            self::STATUS_CUT_OFF => 'secondary',
            self::STATUS_PULLED_OUT => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if the consumer can be reconnected.
     * Pulled out consumers cannot be reconnected.
     */
    public function isReconnectable(): bool
    {
        return $this->status !== self::STATUS_PULLED_OUT;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the block this consumer belongs to.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get the full address (Block X, Lot Y).
     */
    public function getAddressAttribute(): string
    {
        if (! $this->block) {
            return 'No address';
        }

        return $this->block->name.', Lot '.$this->lot_number;
    }

    /**
     * Get the full name via the user relationship.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown';
    }

    /**
     * Get the maintenance requests for this consumer.
     */
    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get the meter readings for this consumer.
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * Get the bills for this consumer.
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get the payments for this consumer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest meter reading value.
     */
    public function getLatestReadingAttribute(): int
    {
        $lastReading = $this->meterReadings()->orderByDesc('billing_period')->first();

        return $lastReading ? $lastReading->reading_value : 0;
    }

    /**
     * Generate the next available ID number.
     */
    public static function generateNextIdNo(): string
    {
        $lastConsumer = self::orderByRaw('CAST(id_no AS UNSIGNED) DESC')->first();

        if ($lastConsumer) {
            $nextNumber = (int) $lastConsumer->id_no + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad to 3 digits (supports up to 999, then 4 digits, etc.)
        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
