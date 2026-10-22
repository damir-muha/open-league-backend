<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'fullName' => $this->full_name,
            'photo' => $this->photo,
            'birthDate' => $this->birth_date,
            'citizenship' => $this->citizenship,
            'position' => $this->position,
			'jersey_number' => $this->whenPivotLoaded('player_season_roster', function () {
                return $this->pivot->jersey_number;
            }),
            'stats' => $this->whenLoaded('seasonStats', function () {
                $stat = $this->seasonStats->first();
                return $stat ? [
                    'games_played' => $stat->games_played,
                    'goals' => $stat->goals,
                    'yellow_cards' => $stat->yellow_cards,
                    'red_cards' => $stat->red_cards,
                ] : null;
            }),
        ];
    }
}
