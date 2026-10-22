<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuspensionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Логика определения команды:
        // Ищем в загруженных заявках игрока ту, которая относится к сезону этой дисквалификации.
        $teamResource = null;
        if ($this->resource->relationLoaded('player') && $this->player->relationLoaded('seasonRosters')) {
            // Находим заявку для текущего сезона
            $roster = $this->player->seasonRosters->firstWhere('season_id', $this->season_id);
            if ($roster && $roster->team) {
                $teamResource = new TeamResource($roster->team);
            }
        }

        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'duration' => $this->duration,
            'served' => $this->served,
            'is_active' => $this->is_active,
            
            // Информация об игроке
            'player' => new PlayerResource($this->whenLoaded('player')),
            
            // Текущая команда игрока
            'team' => $teamResource,

            'season' => new SeasonResource($this->whenLoaded('season')),
            
            'game' => $this->whenLoaded('game', function () {
                return [
                    'id' => $this->game->id,
                    'name' => $this->game->homeTeam->name . ' vs ' . $this->game->awayTeam->name,
                    'date' => $this->game->game_datetime,
                    // Добавляем название тура (Round)
                    'round' => $this->game->round ? $this->game->round->name : null,
                ];
            }),
        ];
    }
}