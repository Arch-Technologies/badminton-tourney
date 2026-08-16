<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'name', 'play_type', 'category', 'format',
        'max_participants', 'points_to_win', 'best_of_games', 'status',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function approvedRegistrations(): HasMany
    {
        return $this->registrations()->where('status', 'approved');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'event_id');
    }

    public function isDoubles(): bool
    {
        return $this->play_type === 'doubles';
    }
}
