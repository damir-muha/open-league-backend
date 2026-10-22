<?php
// --- Файл: app/Filament/Resources/RoundResource/RelationManagers/GameRelationManager.php ---

namespace App\Filament\Resources\RoundResource\RelationManagers;

use App\Filament\Resources\GameResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GameRelationManager extends RelationManager
{
    protected static string $relationship = 'games';

    public function form(Form $form): Form
    {
        $round = $this->getOwnerRecord();
        $teamsForSeason = $round->stage->season->teams()->pluck('name', 'id');

        return $form
            ->schema([
                Forms\Components\Select::make('home_team_id')
                    ->label('Home Team')
                    ->options($teamsForSeason)
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('away_team_id')
                    ->label('Away Team')
                    ->options($teamsForSeason)
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('game_datetime')
                    ->required(),
                Forms\Components\TextInput::make('home_score')->numeric(),
                Forms\Components\TextInput::make('away_score')->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'finished' => 'Finished',
                        'postponed' => 'Postponed',
                    ])
                    ->default('scheduled') // <-- Добавлено значение по умолчанию
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('homeTeam.name'),
                Tables\Columns\TextColumn::make('awayTeam.name'),
                Tables\Columns\TextColumn::make('game_datetime')
                    ->dateTime()
                    ->sortable()
                    ->action(
                        Tables\Actions\Action::make('edit_datetime')
                            ->form([
                                Forms\Components\DateTimePicker::make('game_datetime')
                                    ->label('Game Date & Time')
                                    ->required(),
                            ])
                            ->fillForm(fn (Model $record): array => [
                                'game_datetime' => $record->game_datetime,
                            ])
                            ->action(function (array $data, Model $record): void {
                                $record->update([
                                    'game_datetime' => $data['game_datetime'],
                                ]);
                            })
                    )
                    ->tooltip('Click to quickly edit time')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('home_score'),
                Tables\Columns\TextColumn::make('away_score'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // --- НАЧАЛО ИСПРАВЛЕНИЯ ---
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $round = $this->getOwnerRecord();
                        // Добавляем недостающие ID сезона и этапа
                        $data['season_id'] = $round->stage->season_id;
                        $data['stage_id'] = $round->stage_id;

                        return $data;
                    }),
                // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record): string => GameResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('auto_schedule_times')
                        ->label('Auto-schedule times')
                        ->icon('heroicon-o-clock')
                        ->color('success')
                        ->form([
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Start Time for the first match')
                                ->required()
                                ->default('09:00'),
                            Forms\Components\TextInput::make('interval_minutes')
                                ->label('Interval between matches (minutes)')
                                ->numeric()
                                ->required()
                                ->default(50),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $startTime = \Carbon\Carbon::parse($data['start_time']);
                            $interval = (int) $data['interval_minutes'];

                            // Sort records by ID or existing datetime to maintain order
                            $records = $records->sortBy('id');

                            foreach ($records as $game) {
                                if ($game->game_datetime) {
                                    $currentDate = \Carbon\Carbon::parse($game->game_datetime);
                                    // Preserve the date, change only the time
                                    $newDateTime = $currentDate->copy()->setTime($startTime->hour, $startTime->minute, 0);
                                    
                                    $game->update([
                                        'game_datetime' => $newDateTime,
                                    ]);
                                    
                                    $startTime->addMinutes($interval);
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Times updated successfully!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}