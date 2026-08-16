<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    protected $table = 'game_matches';

    protected $fillable = [
        'event_id', 'bracket_type', 'round', 'match_number',
        'registration1_id', 'registration2_id', 'winner_registration_id',
        'next_match_id', 'next_match_slot', 'loser_next_match_id', 'loser_next_match_slot',
        'court', 'scheduled_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration1(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration1_id');
    }

    public function registration2(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'winner_registration_id');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'next_match_id');
    }

    public function loserNextMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'loser_next_match_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'match_id')->orderBy('game_number');
    }

    public function hasBothParticipants(): bool
    {
        return $this->registration1_id !== null && $this->registration2_id !== null;
    }

    public function isBye(): bool
    {
        return ($this->registration1_id === null) xor ($this->registration2_id === null);
    }
}
