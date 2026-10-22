<?php

// --- Файл: app/Models/Event.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'player_id', 'team_id', 'type',
        'minute', 'related_player_id', 'suspension_duration',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function relatedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'related_player_id');
    }
}