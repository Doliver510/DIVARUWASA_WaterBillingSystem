<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Check if stock is low (below reorder level).
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Add stock quantity.
     */
    public function addStock(int $quantity, ?string $notes = null): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Deduct stock quantity.
     *
     * @throws \Exception If insufficient stock
     */
    public function deductStock(int $quantity): void
    {
        if ($this->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock for {$this->name}. Available: {$this->stock_quantity}, Requested: {$quantity}");
        }

        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Restore stock (e.g., when request is cancelled).
     */
    public function restoreStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }
}
