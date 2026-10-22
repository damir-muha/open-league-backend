<?php

namespace App\Filament\Resources\RoundResource\Pages;

use App\Filament\Resources\RoundResource;
use App\Models\Round;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EditRound extends EditRecord
{
    protected static string $resource = RoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // --- ОБНОВЛЕННАЯ КНОПКА: ТОЛЬКО ДАТА ---
            Actions\Action::make('batch_reschedule')
                ->label('Set round date')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->form([
                    Forms\Components\DatePicker::make('game_date')
                        ->label('Date of matches')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (Round $record, array $data) {
                    $newDate = Carbon::parse($data['game_date']);
                    $updatedCount = 0;

                    // Проходим по всем играм этого тура
                    foreach ($record->games as $game) {
                        // Берем текущее время матча (или 00:00, если его нет)
                        $currentTime = $game->game_datetime 
                            ? Carbon::parse($game->game_datetime) 
                            : Carbon::now()->startOfDay();

                        // Создаем новую дату с СОХРАНЕНИЕМ старого времени
                        $newDateTime = $newDate->copy()->setTime(
                            $currentTime->hour, 
                            $currentTime->minute, 
                            $currentTime->second
                        );

                        $game->update([
                            'game_datetime' => $newDateTime,
                        ]);

                        $updatedCount++;
                    }

                    Notification::make()
                        ->title('Dates updated!')
                        ->body("Updated games: {$updatedCount}. Time remains unchanged.")
                        ->success()
                        ->send();
                }),
            // ---------------------------------------------

            Actions\DeleteAction::make(),
        ];
    }
}