<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'consumer_id',
        'requested_by',
        'request_type',
        'description',
        'status',
        'payment_option',
        'total_material_cost',
        'remarks',
        'requested_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_material_cost' => 'decimal:2',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Request type labels for display.
     */
    public const REQUEST_TYPES = [
        'pipe_leak' => 'Pipe Leak',
        'meter_replacement' => 'Meter Replacement',
        'other' => 'Other',
    ];

    /**
     * Status labels for display.
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Payment option labels for display.
     */
    public const PAYMENT_OPTIONS = [
        'pay_now' => 'Pay Now',
        'charge_to_bill' => 'Charge to Bill',
    ];

    /**
     * Get the consumer who requested the maintenance.
     */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * Get the staff who created the request (if created on behalf of consumer).
     */
    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the materials used in this request.
     */
    public function maintenanceMaterials(): HasMany
    {
        return $this->hasMany(MaintenanceMaterial::class);
    }

    /**
     * Get the request type label.
     */
    public function getRequestTypeLabelAttribute(): string
    {
        return self::REQUEST_TYPES[$this->request_type] ?? $this->request_type;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the payment option label.
     */
    public function getPaymentOptionLabelAttribute(): ?string
    {
        return $this->payment_option ? (self::PAYMENT_OPTIONS[$this->payment_option] ?? $this->payment_option) : null;
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'secondary',
        };
    }

    /**
     * Check if the request can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    /**
     * Check if materials can be added.
     */
    public function canAddMaterials(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the request can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the request can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    /**
     * Recalculate total material cost from materials used.
     */
    public function recalculateTotalCost(): void
    {
        $this->total_material_cost = $this->maintenanceMaterials()->sum('subtotal');
        $this->save();
    }
}
