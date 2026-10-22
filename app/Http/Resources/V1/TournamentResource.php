<?php
// --- Файл: app/Http/Resources/V1/TournamentResource.php ---

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'description' => $this->description,
            'seasons' => SeasonResource::collection($this->whenLoaded('seasons')),
        ];
    }
}