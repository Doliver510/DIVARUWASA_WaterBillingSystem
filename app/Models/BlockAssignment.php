<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'user_id',
    ];

    /**
     * Get the block.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get the meter reader (user).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
