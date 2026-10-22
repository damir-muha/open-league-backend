<?php

// --- Файл: app/Models/Standing.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'team_id', 'group_id', 'round_id', 'position',
        'played', 'wins', 'draws', 'losses', 'goals_for', 'goals_against', 'goal_difference', 'points',
        'home_played', 'home_wins', 'home_draws', 'home_losses', 'home_goals_for', 'home_goals_against', 'home_points',
        'away_played', 'away_wins', 'away_draws', 'away_losses', 'away_goals_for', 'away_goals_against', 'away_points',
        'recent_form',
    ];

    protected $casts = [
        'recent_form' => 'array',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function getFinalPointsAttribute()
    {
        return $this->points - $this->points_deducted;
    }
}