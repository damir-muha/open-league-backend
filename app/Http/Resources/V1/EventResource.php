<?php
// --- Файл: app/Http/Resources/V1/EventResource.php ---
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 
            'minute' => $this->minute,
            'type' => $this->type,
            'team' => new TeamResource($this->whenLoaded('team')),
            'player' => new PlayerResource($this->whenLoaded('player')),
            'relatedPlayer' => new PlayerResource($this->whenLoaded('relatedPlayer')),
        ];
    }
}
