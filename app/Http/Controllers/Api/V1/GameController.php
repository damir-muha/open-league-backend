<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGameEventRequest;
use App\Http\Requests\Api\V1\StoreGameLineupRequest;
use App\Http\Requests\Api\V1\UpdateGameScoreRequest;
use App\Http\Requests\Api\V1\UpdateGameDetailsRequest;
use App\Http\Resources\V1\EventCollection;
use App\Http\Resources\V1\GameCollection;
use App\Http\Resources\V1\GameLineupCollection;
use App\Http\Resources\V1\GameResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GameController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Game::with(['homeTeam', 'awayTeam', 'media', 'stage.season.tournament', 'round']);

        if ($request->has('team_id')) {
            $teamId = $request->team_id;
            $query->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                    ->orWhere('away_team_id', $teamId);
            });
        }

        if ($request->has('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('homeTeam', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('awayTeam', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->has('has_media') && filter_var($request->has_media, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('media');
            // We removed the status check here to allow finished games (VODs) to appear on the Live screen
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
            if ($request->status === 'scheduled') {
                $query->orderBy('game_datetime', 'asc');
            } else {
                $query->orderBy('game_datetime', 'desc');
            }
        } else {
            $query->orderBy('game_datetime', 'desc');
        }

        return new GameCollection($query->paginate());
    }

    public function upcomingRounds(Request $request)
    {
        // 1. Get all active seasons
        $activeSeasons = \App\Models\Season::where('is_current', true)->get();
        $upcomingGames = collect();
        $hasMedia = $request->has('has_media') && filter_var($request->has_media, FILTER_VALIDATE_BOOLEAN);

        foreach ($activeSeasons as $season) {
            $startOfToday = \Carbon\Carbon::today();

            // 1. Try to find the earliest game from TODAY onwards
            $targetGameQuery = Game::where('season_id', $season->id)
                ->where('game_datetime', '>=', $startOfToday);
            
            if ($hasMedia) {
                $targetGameQuery->whereHas('media');
            }

            $targetGame = (clone $targetGameQuery)->orderBy('game_datetime', 'asc')->first();

            // 2. If no future games, find the latest past game, BUT only if it happened within the last 7 days.
            // This prevents old matches (e.g. from a month ago) from showing up forever if a league is paused.
            if (!$targetGame) {
                $pastGameQuery = Game::where('season_id', $season->id)
                    ->where('game_datetime', '<', $startOfToday)
                    ->where('game_datetime', '>=', $startOfToday->copy()->subDays(7));
                
                if ($hasMedia) {
                    $pastGameQuery->whereHas('media');
                }

                $targetGame = (clone $pastGameQuery)->orderBy('game_datetime', 'desc')->first();
            }

            if (!$targetGame) continue; // No games at all in this season matching criteria

            $targetDate = \Carbon\Carbon::parse($targetGame->game_datetime)->format('Y-m-d');
            
            $query = Game::with(['homeTeam', 'awayTeam', 'media', 'stage.season.tournament', 'round'])
                ->where('season_id', $season->id)
                ->whereDate('game_datetime', $targetDate)
                ->orderBy('game_datetime', 'asc');
                
            if ($hasMedia) {
                $query->whereHas('media');
            }

            $upcomingGames = $upcomingGames->merge($query->get());
        }

        // Sort the final merged collection by datetime ascending
        $sortedGames = $upcomingGames->sortBy('game_datetime')->values();

        // Return standard Resource collection
        return new GameCollection($sortedGames);
    }


    public function show(Game $game)
    {
        return new GameResource($game->loadMissing([
            'homeTeam',
            'awayTeam',
            'venue',
            'referees',
            'stage',
            'round',
            'lineups.player',
            'lineups.team',
            'events.player',
            'events.team',
            'media',
            'winnerToGame.round',
            'loserToGame.round',
        ]));
    }

    public function lineups(Game $game)
    {
        // ИСПРАВЛЕНИЕ: Используем get() вместо paginate(), чтобы вернуть ВСЕХ игроков (основа + запас)
        return new GameLineupCollection($game->lineups()->with('player', 'team')->get());
    }

    public function events(Game $game)
    {
        return new EventCollection($game->events()->with(['player', 'team', 'relatedPlayer'])->orderBy('minute')->paginate());
    }

    public function storeLineup(StoreGameLineupRequest $request, Game $game)
    {
        if (Carbon::now()->isAfter($game->game_datetime)) {
            return response()->json(['message' => __('api.match_started')], 403);
        }

        $user = $request->user();
        if (!$user || !$user->person) {
            return response()->json(['message' => __('api.user_not_found')], 403);
        }
        $personTeams = $user->person->teams->pluck('id');


        $managedTeamId = null;
        if ($personTeams->contains($game->home_team_id)) {
            $managedTeamId = $game->home_team_id;
        } elseif ($personTeams->contains($game->away_team_id)) {
            $managedTeamId = $game->away_team_id;
        }

        if (!$managedTeamId) {
            return response()->json(['message' => 'You do not manage either of the teams in this game.'], 403);
        }

        $game->lineups()->where('team_id', $managedTeamId)->delete();

        foreach ($request->validated()['lineups'] as $lineupData) {
            $lineupData['team_id'] = $managedTeamId;
            $game->lineups()->create($lineupData);
        }

        return response()->json(['message' => 'Lineup submitted successfully.'], 201);
    }

    public function destroyLineup(Request $request, Game $game)
    {
        $this->authorize('update', $game);

        if (Carbon::now()->isAfter($game->game_datetime)) {
            return response()->json(['message' => __('api.match_started')], 403);
        }

        $user = $request->user();
        if (!$user || !$user->person) {
            return response()->json(['message' => __('api.user_not_found')], 403);
        }
        $personTeams = $user->person->teams->pluck('id');


        $managedTeamId = null;
        if ($personTeams->contains($game->home_team_id)) {
            $managedTeamId = $game->home_team_id;
        } elseif ($personTeams->contains($game->away_team_id)) {
            $managedTeamId = $game->away_team_id;
        }

        if (!$managedTeamId) {
            return response()->json(['message' => 'You do not manage either of the teams in this game.'], 403);
        }

        $game->lineups()->where('team_id', $managedTeamId)->delete();

        return response()->json(['message' => 'Lineup deleted successfully.'], 200);
    }

    public function updateScore(UpdateGameScoreRequest $request, Game $game)
    {
        $this->authorize('update', $game);

        $game->update($request->validated());
        return new GameResource($game);
    }

    public function storeEvent(StoreGameEventRequest $request, Game $game)
    {
        $event = $game->events()->create($request->validated());
        return new \App\Http\Resources\V1\EventResource($event);
    }

    public function destroyEvent(Game $game, Event $event)
    {
        $this->authorize('update', $game);

        if ($event->game_id !== $game->id) {
            return response()->json(['message' => 'Event does not belong to this game.'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.'], 200);
    }

    public function availablePlayersForLineup(Game $game)
    {
        $homeTeam = $game->homeTeam;
        $awayTeam = $game->awayTeam;
        $season = $game->season;

        if (!$season) {
            return response()->json(['message' => __('api.season_not_found')], 404);
        }

        $getAvailablePlayers = function ($team) use ($season) {
            if (!$team)
                return collect();

            $roster = $team->seasonRosters()
                ->where('season_id', $season->id)
                ->with([
                    'players' => function ($q) {
                        $q->withPivot('jersey_number')->with('suspensions');
                    }
                ])
                ->first();

            if (!$roster)
                return collect();

            return $roster->players->filter(function ($player) {
                return !$player->isSuspended();
            });
        };

        $availableHomePlayers = $getAvailablePlayers($homeTeam);
        $availableAwayPlayers = $getAvailablePlayers($awayTeam);

        return response()->json([
            'home_team' => new \App\Http\Resources\V1\PlayerCollection($availableHomePlayers),
            'away_team' => new \App\Http\Resources\V1\PlayerCollection($availableAwayPlayers),
        ]);
    }

    public function updateDetails(UpdateGameDetailsRequest $request, Game $game)
    {
        $validated = $request->validated();

        if (array_key_exists('venue_id', $validated)) {
            $game->venue_id = $validated['venue_id'];
        }

        if (array_key_exists('referee_ids', $validated)) {
            if (is_array($validated['referee_ids'])) {
                $game->referees()->sync($validated['referee_ids']);
            } else {
                $game->referees()->sync([]);
            }
        }

        $game->save();
        return new GameResource($game->load(['venue', 'referees']));
    }
}