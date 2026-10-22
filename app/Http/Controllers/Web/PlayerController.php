<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Player;
use App\Models\PlayerSeasonStat;
use App\Models\Tournament;

class PlayerController extends Controller
{
    public function show(Player $player)
    {
        // --- Левая и правая колонки ---
        $tournaments = Tournament::whereHas('seasons')->get();
        $advertisements = Advertisement::where('is_active', true)->where('placement_area', 'sidebar')->get();

        // --- Центральная колонка ---
        // Загружаем статистику игрока по всем сезонам, в которых он участвовал
        $statsBySeason = PlayerSeasonStat::where('player_id', $player->id)
            ->with(['season', 'team'])
            ->orderByDesc(function ($query) {
                $query->select('start_date')
                    ->from('seasons')
                    ->whereColumn('seasons.id', 'player_season_stats.season_id')
                    ->limit(1);
            })
            ->get();
            
        return view('players.show', compact('player', 'tournaments', 'advertisements', 'statsBySeason'));
    }
}