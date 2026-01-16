<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'material_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    /**
     * Movement type constants.
     */
    public const TYPE_IN = 'in';

    public const TYPE_OUT = 'out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Reference type constants.
     */
    public const REF_MAINTENANCE = 'maintenance_request';

    public const REF_MANUAL = 'manual_adjustment';

    public const REF_STOCK_IN = 'stock_in';

    public const REF_RESTORE = 'restore';

    /**
     * Get the material for this movement.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the user who made this movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable description of the movement.
     */
    public function getDescriptionAttribute(): string
    {
        $action = match ($this->type) {
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => 'Unknown',
        };

        $reference = match ($this->reference_type) {
            self::REF_MAINTENANCE => "Maintenance Request #{$this->reference_id}",
            self::REF_MANUAL => 'Manual Adjustment',
            self::REF_STOCK_IN => 'Stock Replenishment',
            self::REF_RESTORE => 'Stock Restored (Cancelled)',
            default => '',
        };

        return trim("{$action}: {$reference}");
    }

    /**
     * Scope for movements of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for movements by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
