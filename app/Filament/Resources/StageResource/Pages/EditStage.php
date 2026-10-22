<?php
// app/Filament/Resources/StageResource/Pages/EditStage.php

namespace App\Filament\Resources\StageResource\Pages;

use App\Filament\Resources\StageResource;
use App\Models\Stage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            // --- Старая кнопка генерации (ОСТАВЛЯЕМ, НО НЕ НАЖИМАЕМ) ---
            Actions\Action::make('generate_schedule')
                ->label('Recreate all (DELETES DATA!)')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->form([
                    Forms\Components\TextInput::make('circles')
                        ->label('Number of rounds')
                        ->numeric()->default(1),
                    Forms\Components\CheckboxList::make('days_of_week')
                        ->label('Days of week')
                        ->options([
                            Carbon::MONDAY => 'Monday',
                            Carbon::TUESDAY => 'Tuesday',
                            Carbon::WEDNESDAY => 'Wednesday',
                            Carbon::THURSDAY => 'Thursday',
                            Carbon::FRIDAY => 'Friday',
                            Carbon::SATURDAY => 'Saturday',
                            Carbon::SUNDAY => 'Sunday',
                        ])->columns(2),
                ])
                ->action(function (array $data) {
                    $this->getResource()::handleScheduleGeneration($data, $this->getRecord());
                })
                ->visible(fn (Stage $record) => $record->type !== 'knockout'),

            // --- НОВАЯ КНОПКА: ИСПРАВЛЕНИЕ РАСПИСАНИЯ ---
            Actions\Action::make('reschedule_future')
                ->label('Fix schedule (1 time per week)')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->form([
                    Forms\Components\DatePicker::make('next_game_date')
                        ->label('Date of the next upcoming game')
                        ->helperText('From this date, the countdown for all remaining rounds will begin.')
                        ->required()
                        ->native(false)
                        ->default(now()),
                        
                    Forms\Components\TimePicker::make('game_time')
                        ->label('Game start time (default)')
                        ->required()
                        ->default('00:00'),
                ])
                ->action(function (array $data, Stage $record) {
                    // 1. Получаем дату старта
                    $currentDate = Carbon::parse($data['next_game_date']);
                    $timeParts = explode(':', $data['game_time']);
                    $currentDate->setTime($timeParts[0], $timeParts[1]);

                    // 2. Получаем все туры этого этапа, отсортированные по порядку (id или name)
                    // Мы предполагаем, что id туров идут последовательно
                    $rounds = $record->rounds()->orderBy('id')->get();

                    $updatedCount = 0;

                    foreach ($rounds as $round) {
                        // 3. Проверяем, есть ли в этом туре НЕсыгранные матчи
                        $pendingGames = $round->games()
                            ->where('status', 'scheduled')
                            ->get();

                        if ($pendingGames->isEmpty()) {
                            // Если в туре все матчи сыграны (или их нет), пропускаем его
                            // и НЕ увеличиваем дату, так как этот день уже "прошел"
                            continue;
                        }

                        // 4. Обновляем дату для всех будущих игр в этом туре
                        foreach ($pendingGames as $game) {
                            $game->update([
                                'game_datetime' => $currentDate->copy(),
                            ]);
                            $updatedCount++;
                        }

                        // 5. Переходим к следующей неделе ТОЛЬКО после того, 
                        // как назначили дату текущему туру
                        $currentDate->addWeek(); 
                    }

                    Notification::make()
                        ->title('Schedule updated')
                        ->body("Rescheduled games: {$updatedCount}. Games are now played once a week.")
                        ->success()
                        ->send();
                }),
        ];
    }
}