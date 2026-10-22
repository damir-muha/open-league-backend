<?php
// --- Файл: app/Http/Resources/V1/PersonResource.php (НОВЫЙ) ---
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PersonResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'fullName' => $this->full_name,
            'role' => $this->role,
            'photo' => $this->photo,
            'teams' => $this->whenLoaded('teams', function () {
                return $this->teams->map(fn ($team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'roleInTeam' => $team->pivot->role,
                ]);
            }),
        ];
    }
}