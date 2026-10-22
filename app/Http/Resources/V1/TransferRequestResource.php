<?php
// --- Файл: app/Http/Resources/V1/TransferRequestResource.php ---

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'player' => new PlayerResource($this->whenLoaded('player')),
            'fromTeam' => new TeamResource($this->whenLoaded('fromTeam')),
            'toTeam' => new TeamResource($this->whenLoaded('toTeam')),
            'season' => new SeasonResource($this->whenLoaded('season')),
        ];
    }
}
