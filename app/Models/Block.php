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
        'name',
    ];

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
     * Scope to order blocks numerically (Block 0, Block 1, ... Block 10).
     */
    public function scopeOrdered($query)
    {
        return $query->orderByRaw("CAST(SUBSTRING(name, 7) AS UNSIGNED)");
    }
}
