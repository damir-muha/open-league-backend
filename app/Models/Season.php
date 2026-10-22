<?php

// --- Файл: app/Models/Season.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'name', 'start_date', 'end_date', 'is_current', 'standings_display_mode', 'regulations',
        'description', 'ranking_rules', 'format_type', 'roster_min_players',
        'roster_max_players', 'players_on_field', 'substitutes_on_bench',
        'roster_deadline', 'transfer_window_start', 'transfer_window_end','yellow_cards_for_suspension', 
    ];

    protected $casts = [
        'ranking_rules' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean', 
        'roster_deadline' => 'datetime',
        'transfer_window_start' => 'datetime',
        'transfer_window_end' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'season_team');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}