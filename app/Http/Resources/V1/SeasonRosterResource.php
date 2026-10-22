<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonRosterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'season_id' => $this->season_id,
            'team_id' => $this->team_id,
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}