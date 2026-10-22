<?php

// --- Файл: app/Models/Team.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'description'];

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'season_team');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_team');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'person_team')->withPivot('role')->withTimestamps();
    }

    public function players()
    {
        // Эта сложная связь "проходит" через таблицу 'season_rosters',
        // чтобы найти всех игроков, связанных с командой.
        return $this->hasManyThrough(
            \App\Models\Player::class,       // Модель, которую мы хотим получить в итоге
            \App\Models\SeasonRoster::class, // Модель, через которую мы проходим
            'team_id',                       // Внешний ключ в таблице 'season_rosters' (связь с Team)
            'id',                            // Внешний ключ в таблице 'players' (связь с player_season_roster)
            'id',                            // Локальный ключ в таблице 'teams'
            'id'                           // Локальный ключ в таблице 'season_rosters' (связь с player_season_roster)
        )->join('player_season_roster', 'player_season_roster.season_roster_id', '=', 'season_rosters.id');
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_team');
    }

    public function seasonRosters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SeasonRoster::class);
    }
}