<?php
namespace App\Http\Resources\V1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PlayerSeasonStatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'player' => new PlayerResource($this->whenLoaded('player')),
			'seasonId'=> $this->season->id,
            'season' => $this->season->name,
			'tournament' => $this->season->tournament->name,
            'team' => new TeamResource($this->whenLoaded('team')), 
            'games_played' => $this->games_played,
            'goals' => $this->goals,
            'assists' => $this->assists,
            'yellow_cards' => $this->yellow_cards,
            'red_cards' => $this->red_cards,
        ];
    }
}