<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceMaterial extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'material_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Get the maintenance request this material belongs to.
     */
    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    /**
     * Get the material used.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Calculate subtotal before saving.
     */
    protected static function booted(): void
    {
        static::creating(function (MaintenanceMaterial $record) {
            $record->subtotal = $record->quantity * $record->unit_price;
        });

        static::updating(function (MaintenanceMaterial $record) {
            $record->subtotal = $record->quantity * $record->unit_price;
        });
    }
}
