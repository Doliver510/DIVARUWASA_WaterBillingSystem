<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_number',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'block_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get consumers in this block.
     */
    public function consumers(): HasMany
    {
        return $this->hasMany(Consumer::class);
    }

    /**
     * Get meter readers assigned to this block.
     */
    public function meterReaders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'block_assignments')
            ->withTimestamps();
    }

    /**
     * Scope to get only active blocks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by block number.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('block_number');
    }

    /**
     * Generate name from block number.
     */
    public static function generateName(int $blockNumber): string
    {
        return "Block {$blockNumber}";
    }
}
