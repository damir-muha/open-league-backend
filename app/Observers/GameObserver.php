<?php

namespace App\Observers;

use App\Models\Game;
use App\Models\PlayerSeasonStat;
use App\Models\Standing;
use App\Models\Suspension;
use Illuminate\Support\Facades\DB;

class GameObserver
{
    public function updating(Game $game): void
    {
        if ($game->getOriginal('status') === 'finished') {
            $this->updateStandings($game, 'decrement', true);
            $this->updateGamesPlayedStats($game, 'decrement');

            $this->revertSuspensions($game);
        }
        
    }

    protected function revertSuspensions(Game $game)
    {
        $teamIds = [$game->home_team_id, $game->away_team_id];

        // Находим всех игроков, кто был в составе на этот матч
        $playedInMatchIds = $game->lineups()->pluck('player_id')->toArray();

        // Находим дисквалификации, относящиеся к этому сезону и командам
        // (Ищем даже неактивные, вдруг они только что завершились этим матчем)
        $suspensions = Suspension::where('season_id', $game->season_id)
            ->where('served', '>', 0) // У кого есть что отнимать
            ->get();

        foreach ($suspensions as $suspension) {
            // Проверяем, принадлежит ли игрок одной из команд
            // (Для упрощения проверяем через rosters, но можно и через player_season_roster напрямую)
            $belongsToTeam = \DB::table('player_season_roster')
                ->join('season_rosters', 'player_season_roster.season_roster_id', '=', 'season_rosters.id')
                ->where('season_rosters.season_id', $game->season_id)
                ->whereIn('season_rosters.team_id', $teamIds)
                ->where('player_season_roster.player_id', $suspension->player_id)
                ->exists();

            if ($belongsToTeam) {
                // Если игрока НЕ БЫЛО в заявке (значит, он "пропускал" этот матч)
                if (!in_array($suspension->player_id, $playedInMatchIds)) {
                    
                    // Уменьшаем счетчик
                    $suspension->decrement('served');
                    
                    // Если дисквалификация была завершена, но мы откатили матч — снова делаем её активной
                    if (!$suspension->is_active && $suspension->served < $suspension->duration) {
                        $suspension->update(['is_active' => true]);
                    }
                }
            }
        }
    }

    public function updated(Game $game): void
    {
        if ($game->status === 'finished') {
            $this->updateStandings($game, 'increment');
            $this->updateGamesPlayedStats($game, 'increment');
            $this->processSuspensions($game);
        }
    }

    protected function updateStandings(Game $game, string $action, bool $useOriginal = false): void
    {
        $game->loadMissing('stage', 'season');
        if ($game->stage && $game->stage->type === 'knockout') {
            return;
        }

        // Вызываем сервис полного перерасчета
        // Это безопасно и гарантирует идеальную синхронизацию таблицы со всеми правилами сортировки
        app(\App\Services\StandingCalculationService::class)->recalculateForSeason($game->season);
    }

    protected function updateGamesPlayedStats(Game $game, string $action)
    {
        if (!$game->relationLoaded('lineups')) {
            $game->load('lineups');
        }

        foreach ($game->lineups as $lineup) {
            $stat = PlayerSeasonStat::firstOrCreate([
                'player_id' => $lineup->player_id,
                'season_id' => $game->season_id,
                'team_id'   => $lineup->team_id,
            ]);

            if ($action === 'increment') {
                $stat->increment('games_played');
            } elseif ($action === 'decrement' && $stat->games_played > 0) {
                $stat->decrement('games_played');
            }
        }
    }

    // --- ОПТИМИЗИРОВАННЫЙ МЕТОД ---
    protected function processSuspensions(Game $game)
    {
        $suspensions = Suspension::where('season_id', $game->season_id)
            ->where('is_active', true)
            ->whereColumn('served', '<', 'duration')
            ->get();

        if ($suspensions->isEmpty()) {
            return;
        }

        $teamIds = [$game->home_team_id, $game->away_team_id];

        // 1. Получаем ID всех дисквалифицированных игроков, которые ЕСТЬ в заявках этих команд
        // Делаем это одним запросом
        $playingSuspendedPlayerIds = DB::table('player_season_roster')
            ->join('season_rosters', 'player_season_roster.season_roster_id', '=', 'season_rosters.id')
            ->where('season_rosters.season_id', $game->season_id)
            ->whereIn('season_rosters.team_id', $teamIds)
            ->whereIn('player_season_roster.player_id', $suspensions->pluck('player_id'))
            ->pluck('player_season_roster.player_id')
            ->toArray();

        // 2. Получаем ID всех игроков, кто был в составе на матч
        $playedInMatchIds = $game->lineups()->pluck('player_id')->toArray();

        foreach ($suspensions as $suspension) {
            // Если игрок принадлежит одной из играющих команд
            if (in_array($suspension->player_id, $playingSuspendedPlayerIds)) {
                // И если его НЕ было в составе на матч (он пропустил игру)
                if (!in_array($suspension->player_id, $playedInMatchIds)) {
                    $suspension->increment('served');

                    if ($suspension->fresh()->served >= $suspension->duration) {
                        $suspension->update(['is_active' => false]);
                    }
                }
            }
        }
    }
}