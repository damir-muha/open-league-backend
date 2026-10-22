<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Game;
use App\Models\News;
use App\Models\PlayerSeasonStat;
use App\Models\Season;
use App\Models\Standing;
use App\Models\Tournament;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // --- Левая колонка ---
        $tournaments = Tournament::whereHas('seasons')->with(['seasons' => function ($query) {
            $query->orderBy('start_date', 'desc');
        }])->get();

        // --- Определяем активный сезон ---
        $selectedSeason = null;
        if ($request->has('season')) {
            $selectedSeason = Season::with('tournament')->find($request->input('season'));
        } else {
            $selectedSeason = Season::where('is_current', true)->with('tournament')->first();
        }

        // --- Центральная колонка (данные для вкладок) ---
        $gamesByStatusAndRound = null;
        $standings = null;
        $statsLeaders = [ 'top_scorers' => collect(), 'top_yellow_cards' => collect(), 'top_red_cards' => collect() ];
        $seasonsForDropdown = collect();

        if ($selectedSeason) {
            // Данные для выпадающего списка сезонов
            $seasonsForDropdown = Season::where('tournament_id', $selectedSeason->tournament_id)
                                        ->orderBy('start_date', 'desc')
                                        ->get();

            // --- ИСПРАВЛЕНИЕ СОРТИРОВКИ ---
            // Матчи, сгруппированные по статусу и по турам (сортировка от ранних к поздним)
            $gamesByStatusAndRound = Game::where('season_id', $selectedSeason->id)
                ->with(['homeTeam', 'awayTeam', 'round'])
                ->orderBy('game_datetime', 'asc') // <-- ИЗМЕНЕНО
                ->get()
                ->groupBy(['status', 'round.name']);

            // Турнирная таблица
            $standings = Standing::where('season_id', $selectedSeason->id)
                ->with('team')
                ->orderBy('points', 'desc')
                ->orderBy('goal_difference', 'desc')
                ->get();

            // Лидеры статистики 
            $baseStatsQuery = PlayerSeasonStat::where('season_id', $selectedSeason->id)->with(['player', 'team']);
            $statsLeaders['top_scorers'] = (clone $baseStatsQuery)->where('goals', '>', 0)->orderBy('goals', 'desc')->limit(15)->get();
            $statsLeaders['top_yellow_cards'] = (clone $baseStatsQuery)->where('yellow_cards', '>', 0)->orderBy('yellow_cards', 'desc')->limit(15)->get();
            $statsLeaders['top_red_cards'] = (clone $baseStatsQuery)->where('red_cards', '>', 0)->orderBy('red_cards', 'desc')->limit(15)->get();
        }
        
        // Новости для слайдера
        $sliderNews = News::latest('published_at')->limit(8)->get();

        // --- Правая колонка ---
        $advertisements = Advertisement::where('is_active', true)
            ->where('placement_area', 'sidebar')
            ->get();

        return view('home', compact(
            'tournaments', 
            'sliderNews', 
            'advertisements',
            'selectedSeason',
            'seasonsForDropdown', // <-- ДОБАВЛЕНО
            'gamesByStatusAndRound',
            'standings',
            'statsLeaders'
        ));
    }
}