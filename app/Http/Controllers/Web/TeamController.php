<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Game;
use App\Models\PlayerSeasonStat;
use App\Models\Season;
use App\Models\Team;
use App\Models\Tournament;

class TeamController extends Controller
{
    public function show(Team $team)
    {
        // --- Левая и правая колонки (без изменений) ---
        $tournaments = Tournament::whereHas('seasons')->get();
        $advertisements = Advertisement::where('is_active', true)->where('placement_area', 'sidebar')->get();

        // --- Центральная колонка ---
        $currentSeason = Season::where('is_current', true)->first();
        $roster = null;
        $playerStats = collect(); // Создаем пустую коллекцию для статистики

        if ($currentSeason) {
            $roster = $team->seasonRosters()
                ->where('season_id', '=', $currentSeason->id)
                ->with('players')
                ->first();

            // --- ВОТ ГЛАВНОЕ ИЗМЕНЕНИЕ ---
            // Если заявка найдена, получаем ID всех игроков из этой заявки
            if ($roster) {
                $playerIds = $roster->players->pluck('id');
                // Загружаем статистику только для этих игроков и в текущем сезоне
                $playerStats = PlayerSeasonStat::where('season_id', $currentSeason->id)
                    ->whereIn('player_id', $playerIds)
                    ->get()
                    ->keyBy('player_id'); // Группируем по ID игрока для удобного доступа
            }
            // --- КОНЕЦ ИЗМЕНЕНИЯ ---
        }
        
        // ... (запросы для recentGames и upcomingGames без изменений) ...
        $recentGames = Game::where(function ($query) use ($team) {
                $query->where('home_team_id', $team->id)
                      ->orWhere('away_team_id', $team->id);
            })
            ->where('status', 'finished')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('game_datetime', 'desc')
            ->limit(5)
            ->get();
            
        $upcomingGames = Game::where(function ($query) use ($team) {
                $query->where('home_team_id', $team->id)
                      ->orWhere('away_team_id', $team->id);
            })
            ->where('status', 'scheduled')
            ->with(['homeTeam', 'awayTeam', 'season.tournament'])
            ->orderBy('game_datetime', 'asc')
            ->limit(5)
            ->get();

        return view('teams.show', compact('team', 'tournaments', 'advertisements', 'roster', 'recentGames', 'upcomingGames', 'playerStats'));
    }
}