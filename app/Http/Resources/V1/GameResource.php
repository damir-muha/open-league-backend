<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seasonId' => $this->season_id,
            'tournamentName' => $this->whenLoaded('stage', fn() => $this->stage->season?->tournament?->name),
            'stageName' => $this->whenLoaded('stage', fn() => $this->stage->name),
            'roundName' => $this->whenLoaded('round', fn() => $this->round->name),
            'groupName' => $this->whenLoaded('group', fn() => $this->group->name),
            'homeTeam' => new TeamResource($this->whenLoaded('homeTeam')),
            'awayTeam' => new TeamResource($this->whenLoaded('awayTeam')),
            'homeScore' => $this->home_score,
            'awayScore' => $this->away_score,
            'homePenaltyScore' => $this->home_penalty_score,
            'awayPenaltyScore' => $this->away_penalty_score,
            'status' => $this->status,
            'datetime' => $this->game_datetime,
            'venue' => $this->whenLoaded('venue'),
            'referees' => PersonResource::collection($this->whenLoaded('referees')),
            'lineups' => GameLineupResource::collection($this->whenLoaded('lineups')),
            'events' => EventResource::collection($this->whenLoaded('events')),
            'media' => new GameMediumCollection($this->whenLoaded('media')),

            // --- НАЧАЛО ИСПРАВЛЕНИЯ ---
            'playoff_info' => $this->when($this->winner_to_game_id || $this->loser_to_game_id, function () {
                return [
                    'winner_advances_to' => $this->whenLoaded('winnerToGame', function () {
                        // Проверяем, что связь winnerToGame существует, прежде чем получить ее свойства
                        if ($this->winnerToGame) {
                            return [
                                'game_id' => $this->winnerToGame->id,
                                'round_name' => $this->winnerToGame->round?->name,
                            ];
                        }
                        return null;
                    }),
                    'loser_advances_to' => $this->whenLoaded('loserToGame', function () {
                        // Аналогичная проверка для loserToGame
                        if ($this->loserToGame) {
                            return [
                                'game_id' => $this->loserToGame->id,
                                'round_name' => $this->loserToGame->round?->name,
                            ];
                        }
                        return null;
                    }),
                ];
            }),
            // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
        ];
    }
}