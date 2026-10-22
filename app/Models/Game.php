<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'stage_id', 'round_id', 'group_id', 'home_team_id',
        'away_team_id', 'venue_id', 'referee_id', 'game_datetime', 'status',
        'home_score', 'away_score', 'home_penalty_score', 'away_penalty_score', 
        'winner_to_game_id', 'loser_to_game_id',
    ];

    protected $casts = [
        'game_datetime' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function referees(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'game_person')->withPivot('role');
    }

    public function media(): HasMany
    {
        return $this->hasMany(GameMedium::class);
    }
    
    public function lineups(): HasMany
    {
        return $this->hasMany(GameLineup::class)->orderBy('status', 'desc');
    }

    /**
     * Связь для определения следующего матча для победителя.
     */
    public function winnerToGame(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'winner_to_game_id');
    }

    /**
     * Связь для определения следующего матча для проигравшего.
     */
    public function loserToGame(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'loser_to_game_id');
    }
}