<?php
// --- Файл: app/Http/Resources/V1/GameLineupResource.php ---
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameLineupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
       // --- Новая, более надежная логика для получения номера ---
        $jerseyNumber = null;
        
        // Загружаем только те связи, которые точно нужны
        $this->loadMissing(['game.season', 'player']);

        // Получаем сезон, к которому относится игра
        $season = $this->game->season;

        if ($season && $this->player) {
            // Чтобы избежать проблем со связями Eloquent, делаем прямой и надежный запрос
            // к связующей таблице `player_season_roster`
            // Добавляем `\` перед DB, чтобы PHP искал класс в глобальном пространстве имен
            $pivotData = \DB::table('player_season_roster')
                // Соединяем с основной таблицей заявок, чтобы получить season_id
                ->join('season_rosters', 'player_season_roster.season_roster_id', '=', 'season_rosters.id')
                // Фильтруем по ID сезона текущей игры
                ->where('season_rosters.season_id', $season->id)
                // Фильтруем по ID текущего игрока
                ->where('player_season_roster.player_id', $this->player->id)
                // Выбираем только нужное поле
                ->select('player_season_roster.jersey_number')
                ->first();

            // Если данные найдены, присваиваем номер
            if ($pivotData) {
                $jerseyNumber = $pivotData->jersey_number;
            }
        }
        // --- Конец логики ---

        return [
            'team' => new TeamResource($this->whenLoaded('team')),
            'player' => new PlayerResource($this->whenLoaded('player')),
            'jersey_number' => $jerseyNumber,
            'status' => $this->status,
        ];
    }
}