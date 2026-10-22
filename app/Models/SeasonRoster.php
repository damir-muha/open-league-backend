<?php

// --- Файл: app/Models/SeasonRoster.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SeasonRoster extends Model
{
    use HasFactory;

    protected $fillable = ['season_id', 'team_id'];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_season_roster')
            ->withPivot('jersey_number');;
    }
}