<?php 

namespace App\Observers;

use App\Models\Event;
use App\Models\PlayerSeasonStat;
use App\Models\Suspension;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        $this->updatePlayerStats($event, 'increment');
        $this->handleSuspensions($event);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        // При удалении события мы уменьшаем статистику
        $this->updatePlayerStats($event, 'decrement');

        // Удаляем прямые дисквалификации (красная или вторая желтая)
        if (in_array($event->type, ['red_card', 'second_yellow_card'])) {
            Suspension::where('game_id', $event->game_id)
                ->where('player_id', $event->player_id)
                ->whereIn('reason', ['red_card', 'second_yellow_card'])
                ->delete();
        }

        // Удаляем накопительную дисквалификацию, если удалена обычная желтая карточка
        if ($event->type === 'yellow_card') {
            Suspension::where('game_id', $event->game_id)
                ->where('player_id', $event->player_id)
                ->where('reason', 'yellow_card_accumulation')
                ->delete();
        }
    }

    public function updated(Event $event): void
    {
        // Если изменился тип карточки или длительность
        if ($event->isDirty('type') || $event->isDirty('suspension_duration')) {
            // Находим связанную дисквалификацию
            $suspension = Suspension::where('game_id', $event->game_id)
                ->where('player_id', $event->player_id)
                ->whereIn('reason', ['red_card', 'second_yellow_card'])
                ->first();

            if ($suspension) {
                // Обновляем данные
                $suspension->update([
                    'reason' => $event->type,
                    'duration' => $event->suspension_duration ?? 1,
                ]);
            }
        }
    }

    /**
     * Обновляет (увеличивает или уменьшает) статистику игрока.
     */
    protected function updatePlayerStats(Event $event, string $action = 'increment')
    {
        if (!$event->player_id || !$event->team_id) {
            return;
        }

        $stat = PlayerSeasonStat::firstOrCreate([
            'player_id' => $event->player_id,
            'season_id' => $event->game->season_id,
            'team_id'   => $event->team_id,
        ]);

        $column = match ($event->type) {
            'goal'                 => 'goals',
            'assist'               => 'assists',
            'yellow_card'          => 'yellow_cards',
            'second_yellow_card'   => 'red_cards',
            'red_card'             => 'red_cards',
            default                => null,
        };

        if ($column) {
            if ($action === 'increment') {
                $stat->increment($column);
            } elseif ($action === 'decrement' && $stat->$column > 0) {
                $stat->decrement($column);
            }
        }
    }

    /**
     * Обрабатывает логику создания дисквалификаций.
     */
    protected function handleSuspensions(Event $event)
    {
        if (!in_array($event->type, ['yellow_card', 'second_yellow_card', 'red_card'])) {
            return;
        }
        
        $player = $event->player;
        $game = $event->game;
        $season = $game->season;

        // Обработка прямой красной или второй желтой (удаление)
        if ($event->type === 'second_yellow_card' || $event->type === 'red_card') {
            Suspension::create([
                'player_id' => $player->id, 
                'season_id' => $season->id, 
                'game_id' => $game->id,
                'reason' => $event->type, 
                'duration' => $event->suspension_duration ?? 1,
                'is_active' => true,
            ]);
        }

        // Обработка накопления желтых карточек
        if ($event->type === 'yellow_card') {
            $suspensionThreshold = $season->yellow_cards_for_suspension; 
            
            if (!$suspensionThreshold || $suspensionThreshold <= 0) return;

            // --- ИЗМЕНЕНИЕ: Добавлен фильтр по команде (where team_id) ---
            $totalYellowsInSeason = Event::where('player_id', $player->id)
                ->where('team_id', $event->team_id) // Считаем карточки только за ЭТУ команду
                ->whereHas('game', fn ($q) => $q->where('season_id', $season->id))
                ->whereIn('type', ['yellow_card', 'second_yellow_card'])
                ->count();
            
            // Если количество кратно порогу (например, 3, 6, 9...), создаем дисквалификацию
            if ($totalYellowsInSeason > 0 && $totalYellowsInSeason % $suspensionThreshold === 0) {
                 Suspension::create([
                    'player_id' => $player->id, 
                    'season_id' => $season->id, 
                    'game_id' => $game->id,
                    'reason' => 'yellow_card_accumulation', 
                    'duration' => 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}