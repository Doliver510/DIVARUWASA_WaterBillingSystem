<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['full_name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get the user's formal name (Last Name, First Name M.I.).
     */
    public function getFormalNameAttribute(): string
    {
        $middleInitial = $this->middle_name ? strtoupper(substr($this->middle_name, 0, 1)).'.' : '';

        return trim($this->last_name.', '.$this->first_name.' '.$middleInitial);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function consumer(): HasOne
    {
        return $this->hasOne(Consumer::class);
    }

    /**
     * Get the blocks assigned to this user (for meter readers).
     */
    public function assignedBlocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'block_assignments')
            ->withTimestamps();
    }

    /**
     * Check if this user is a meter reader.
     */
    public function isMeterReader(): bool
    {
        return $this->role?->slug === 'meter-reader';
    }
}
