<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GameCollection;
use App\Http\Resources\V1\SeasonCollection;
use App\Http\Resources\V1\SeasonResource;
use App\Http\Resources\V1\StandingResource;
use App\Http\Resources\V1\TeamCollection;
use App\Models\Game;
use App\Models\Season;
use App\Models\Standing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SeasonController extends Controller
{
    public function index()
    {
        // Список сезонів змінюється рідко, кешуємо на 10 хвилин
        $seasons = Cache::remember('api_seasons_list', 600, function () {
            return Season::with('tournament')
                ->orderBy('is_current', 'desc')
                ->orderBy('start_date', 'desc')
                ->paginate();
        });
            
        return new SeasonCollection($seasons);
    }

    public function show(Season $season)
    {
        // Детальна інфо про сезон - кешуємо на 10 хвилин
        $cacheKey = 'api_season_show_' . $season->id;
        
        $seasonData = Cache::remember($cacheKey, 600, function () use ($season) {
            return $season->loadMissing(['tournament', 'teams', 'stages.rounds', 'stages.groups']);
        });

        return new SeasonResource($seasonData);
    }

    public function teams(Season $season)
    {
        // Список команд сезону - кешуємо на 10 хвилин
        return Cache::remember('api_season_teams_' . $season->id . '_page_' . request('page', 1), 600, function () use ($season) {
            return new TeamCollection($season->teams()->paginate());
        });
    }

    public function games(Request $request, Season $season)
    {
        if ($request->input('format') === 'bracket') {
            
            $cacheKey = 'api_season_bracket_' . $season->id;

            // Кешуємо сітку на 2 хвилини
            $response = Cache::remember($cacheKey, 120, function () use ($season) {
                $playoffGames = $season->games()
                    ->whereHas('stage', function ($query) {
                        $query->where('type', 'knockout');
                    })
                    ->with([
                        'homeTeam', 'awayTeam', 'round', 'stage', 
                        'winnerToGame.round', 'loserToGame.round', 'media'
                    ])
                    ->orderBy('game_datetime')
                    ->get();

                $groupedByStage = $playoffGames->groupBy(function($game) {
                    return $game->stage ? $game->stage->name : 'Unknown Stage';
                });

                return $groupedByStage->map(function ($stageMatches, $stageName) {
                    return [
                        'stage_name' => $stageName,
                        'rounds' => $stageMatches->groupBy(function($game) {
                            return $game->round ? $game->round->name : 'Unknown Round';
                        })->map(function ($matches, $roundName) {
                            return [
                                'round_name' => $roundName,
                                'matches' => \App\Http\Resources\V1\GameResource::collection($matches),
                            ];
                        })->values(),
                    ];
                })->values();
            });

            return response()->json(['data' => $response]);
        }

        // 2. Звичайний список ігор (Infinite Scroll)
        // Тут ми прибрали кеш, щоб дані завжди були актуальними (рахунок Live)
        // Швидкість забезпечується індексами в БД
        
        $query = $season->games()
            ->with(['homeTeam', 'awayTeam', 'round', 'stage', 'media', 'group']); // Загружаем group для отображения названия группы

        // ФІЛЬТРАЦІЯ ПО СТАТУСУ
        // Якщо мобільний додаток запитує конкретний статус ('scheduled' або 'finished')
        if ($request->has('status') && in_array($request->status, ['scheduled', 'finished'])) {
            $query->where('status', $request->status);
        }

        // СОРТУВАННЯ
        if ($request->status === 'finished') {
            // Для завершених: Зверху найсвіжіші (вчора, позавчора...)
            $query->orderBy('game_datetime', 'desc');
        } else {
            // Для майбутніх (і вкладки "Всі", якщо буде): Зверху найближчі (завтра, післязавтра...)
            // Це забезпечить правильний порядок турів: Тур 1, Тур 2, Тур 3... навіть для різних груп
            $query->orderBy('game_datetime', 'asc');
        }

        $perPage = $request->input('per_page', 15);
        
        // Повертаємо по $perPage штук на сторінку
        return new GameCollection($query->paginate($perPage));
    }

    public function standings(Request $request, Season $season)
    {
        $roundId = $request->input('round_id');
        $stageId = $request->input('stage_id');
        $cacheKey = 'api_season_standings_' . $season->id . '_stage_' . ($stageId ?? 'all') . '_round_' . ($roundId ?? 'all');

        // КЕШУЄМО ТАБЛИЦЮ НА 2 ХВИЛИНИ (120 секунд)
        $standings = Cache::remember($cacheKey, 120, function () use ($season, $stageId, $roundId) {
            $query = Standing::where('season_id', $season->id)
                ->with(['team', 'group']);
                
            if ($stageId) {
                $query->where(function ($q) use ($stageId) {
                    $q->whereHas('group', function ($q2) use ($stageId) {
                        $q2->where('stage_id', $stageId);
                    })->orWhereNull('group_id');
                });
            }
                
            if ($roundId) {
                $query->where('round_id', $roundId);
            } else {
                $query->whereNull('round_id');
            }

            return $query->orderBy('group_id')
                ->orderBy('position')
                ->get();
        });
        
        return StandingResource::collection($standings);
    }
    
    public function registeredPlayers(Season $season)
    {
        // Список ID гравців - кешуємо на 10 хвилин
        $cacheKey = 'api_season_registered_players_' . $season->id;

        $playerIds = Cache::remember($cacheKey, 600, function () use ($season) {
            return DB::table('player_season_roster')
                ->join('season_rosters', 'player_season_roster.season_roster_id', '=', 'season_rosters.id')
                ->where('season_rosters.season_id', $season->id)
                ->pluck('player_id');
        });

        return response()->json(['data' => $playerIds]);
    }

    public function statsLeaders(Season $season)
    {
        $cacheKey = 'api_season_stats_leaders_' . $season->id;

        // КЕШУЄМО ЛІДЕРІВ НА 5 ХВИЛИН (300 секунд)
        $data = Cache::remember($cacheKey, 300, function () use ($season) {
            $baseQuery = \App\Models\PlayerSeasonStat::where('season_id', $season->id)
                                    ->with(['player', 'team']) 
                                    ->limit(10); 
            
            return [
                'top_scorers' => (clone $baseQuery)->where('goals', '>', 0)->orderBy('goals', 'desc')->get(),
                'top_assists' => (clone $baseQuery)->where('assists', '>', 0)->orderBy('assists', 'desc')->get(),
                'top_yellow_cards' => (clone $baseQuery)->where('yellow_cards', '>', 0)->orderBy('yellow_cards', 'desc')->get(),
                'top_red_cards' => (clone $baseQuery)->where('red_cards', '>', 0)->orderBy('red_cards', 'desc')->get(),
            ];
        });
        
        return response()->json([
            'data' => [
                'top_scorers' => \App\Http\Resources\V1\PlayerSeasonStatResource::collection($data['top_scorers']),
                'top_assists' => \App\Http\Resources\V1\PlayerSeasonStatResource::collection($data['top_assists']),
                'top_yellow_cards' => \App\Http\Resources\V1\PlayerSeasonStatResource::collection($data['top_yellow_cards']),
                'top_red_cards' => \App\Http\Resources\V1\PlayerSeasonStatResource::collection($data['top_red_cards']),
            ]
        ]);
    }
}