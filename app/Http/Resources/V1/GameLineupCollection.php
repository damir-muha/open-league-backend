<?php
// --- Файл: app/Http/Resources/V1/GameLineupCollection.php (НОВЫЙ) ---
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GameLineupCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}