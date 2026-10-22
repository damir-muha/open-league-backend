<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Game;
use App\Models\Tournament;

class GameController extends Controller
{
    public function show(Game $game)
    {
        // --- Левая и правая колонки ---
        $tournaments = Tournament::whereHas('seasons')->get();
        $advertisements = Advertisement::where('is_active', true)->where('placement_area', 'sidebar')->get();

        // --- Центральная колонка ---
        // Загружаем всю необходимую информацию о матче за один запрос
        $game->load([
            'homeTeam', 
            'awayTeam', 
            'season.tournament', 
            'lineups.player', // Включая информацию об игроке в составе
            'events' => function ($query) {
                $query->with(['player', 'team'])->orderBy('minute', 'asc'); // Сразу сортируем события
            }
        ]);

        // Разделяем составы на домашнюю и гостевую команды
        $homeLineup = $game->lineups->where('team_id', $game->home_team_id);
        $awayLineup = $game->lineups->where('team_id', $game->away_team_id);

        return view('games.show', compact('game', 'tournaments', 'advertisements', 'homeLineup', 'awayLineup'));
    }
}