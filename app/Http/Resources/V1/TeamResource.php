<?php
// --- Файл: app/Http/Resources/V1/TeamResource.php ---
namespace App\Http\Resources\V1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TeamResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
			'id' => $this->id, 
			'name' => $this->name, 
			'logo' => $this->logo,
			'season' => new SeasonResource($this->whenLoaded('season')),
			'rosters' => SeasonRosterResource::collection($this->whenLoaded('seasonRosters')),
		];
    }
}