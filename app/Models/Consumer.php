<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consumer extends Model
{
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
