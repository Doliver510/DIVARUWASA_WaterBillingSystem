<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Material extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'unit_price',
        'stock_quantity',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'reorder_level' => 'integer',
        ];
    }

    /**
     * Get the maintenance materials (usage records) for this material.
     */
    public function maintenanceMaterials(): HasMany
    {
        return $this->hasMany(MaintenanceMaterial::class);
    }

    /**
     * Get the stock movements for this material.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if stock is low (below reorder level).
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Add stock quantity with audit logging.
     */
    public function addStock(int $quantity, ?string $remarks = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        $stockBefore = $this->stock_quantity;
        $this->increment('stock_quantity', $quantity);
        $stockAfter = $this->fresh()->stock_quantity;

        $this->logMovement(
            StockMovement::TYPE_IN,
            $quantity,
            $stockBefore,
            $stockAfter,
            $referenceType ?? StockMovement::REF_STOCK_IN,
            $referenceId,
            $remarks
        );
    }

    /**
     * Deduct stock quantity with audit logging.
     *
     * @throws \Exception If insufficient stock
     */
    public function deductStock(int $quantity, ?string $remarks = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        if ($this->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock for {$this->name}. Available: {$this->stock_quantity}, Requested: {$quantity}");
        }

        $stockBefore = $this->stock_quantity;
        $this->decrement('stock_quantity', $quantity);
        $stockAfter = $this->fresh()->stock_quantity;

        $this->logMovement(
            StockMovement::TYPE_OUT,
            -$quantity, // Negative for out
            $stockBefore,
            $stockAfter,
            $referenceType ?? StockMovement::REF_MAINTENANCE,
            $referenceId,
            $remarks
        );
    }

    /**
     * Restore stock (e.g., when request is cancelled) with audit logging.
     */
    public function restoreStock(int $quantity, ?string $remarks = null, ?int $maintenanceRequestId = null): void
    {
        $stockBefore = $this->stock_quantity;
        $this->increment('stock_quantity', $quantity);
        $stockAfter = $this->fresh()->stock_quantity;

        $this->logMovement(
            StockMovement::TYPE_IN,
            $quantity,
            $stockBefore,
            $stockAfter,
            StockMovement::REF_RESTORE,
            $maintenanceRequestId,
            $remarks ?? 'Stock restored due to cancelled request'
        );
    }

    /**
     * Log a stock movement to the audit table.
     */
    protected function logMovement(
        string $type,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null
    ): void {
        StockMovement::create([
            'material_id' => $this->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'remarks' => $remarks,
        ]);
    }
}
