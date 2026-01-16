<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target_audience',
        'starts_at',
        'ends_at',
        'send_email',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'send_email' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user who created this announcement.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Get only active announcements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get current announcements (within date range).
     */
    public function scopeCurrent($query)
    {
        return $query->where('starts_at', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now()->toDateString());
            });
    }

    /**
     * Scope: Filter by target audience.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('target_audience', 'all')
                ->orWhere('target_audience', $audience);
        });
    }

    /**
     * Get the type badge color.
     */
    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'info' => 'blue',
            'warning' => 'yellow',
            'urgent' => 'red',
            default => 'secondary',
        };
    }

    /**
     * Get the type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'info' => 'info-circle',
            'warning' => 'alert-triangle',
            'urgent' => 'alert-circle',
            default => 'bell',
        };
    }

    /**
     * Get current announcements for a specific user role.
     */
    public static function getCurrentForRole(?string $roleSlug): \Illuminate\Database\Eloquent\Collection
    {
        $audience = match ($roleSlug) {
            'consumer' => 'consumers',
            'admin', 'cashier', 'meter_reader', 'maintenance_staff' => 'staff',
            default => 'all',
        };

        return self::active()
            ->current()
            ->forAudience($audience)
            ->orderByRaw("FIELD(type, 'urgent', 'warning', 'info')")
            ->latest()
            ->get();
    }
}
