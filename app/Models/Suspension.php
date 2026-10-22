<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suspension extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 
        'season_id', 
        'game_id', 
        'reason', 
        'duration', 
        'served', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}