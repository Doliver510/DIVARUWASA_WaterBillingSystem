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
        'address',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the meter readings for this consumer.
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
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
