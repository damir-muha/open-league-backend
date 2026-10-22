<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GameResource;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\Round;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Filters\Filter;
use Filament\Forms;

class UpcomingGamesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Календарь игр';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Game::query()
                    ->with(['homeTeam', 'awayTeam', 'season.tournament', 'round'])
            )
            ->defaultSort('game_datetime', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('game_datetime')
                    ->label('Дата')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->action(
                        Tables\Actions\Action::make('edit_datetime')
                            ->form([
                                Forms\Components\DateTimePicker::make('game_datetime')
                                    ->label('Game Date & Time')
                                    ->required(),
                            ])
                            ->fillForm(fn (Game $record): array => [
                                'game_datetime' => $record->game_datetime,
                            ])
                            ->action(function (array $data, Game $record): void {
                                $record->update([
                                    'game_datetime' => $data['game_datetime'],
                                ]);
                            })
                    )
                    ->tooltip('Click to quickly edit time')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('season.tournament.name')
                    ->label('Турнир')
                    ->sortable(),
                Tables\Columns\TextColumn::make('season.name')
                    ->label('Сезон'),
                Tables\Columns\TextColumn::make('round.name')
                    ->label('Тур')
                    ->sortable(),
                Tables\Columns\TextColumn::make('homeTeam.name')
                    ->label('Хозяева')
                    ->searchable(),
                Tables\Columns\TextColumn::make('awayTeam.name')
                    ->label('Гости')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'finished',
                        'danger' => 'postponed',
                    ]),
            ])
            ->filters([
                // Объединенный фильтр для Турнира и Тура
                Filter::make('criteria')
                    ->form([
                        Select::make('tournament_id')
                            ->label('Турнир')
                            ->options(Tournament::all()->pluck('name', 'id'))
                            ->searchable()
                            ->live() // Делаем поле реактивным
                            ->afterStateUpdated(fn (Set $set) => $set('round_id', null)), // Сбрасываем тур при смене турнира

                        Select::make('round_id')
                            ->label('Тур')
                            ->options(function (Get $get) {
                                $tournamentId = $get('tournament_id');
                                if (!$tournamentId) {
                                    return []; // Пока турнир не выбран, список пуст (или можно вернуть все)
                                }
                                
                                // Выбираем только туры, которые относятся к сезонам выбранного турнира
                                return Round::whereHas('stage.season', function ($q) use ($tournamentId) {
                                    $q->where('tournament_id', $tournamentId);
                                })
                                ->with('stage')
                                ->get()
                                ->mapWithKeys(function ($round) {
                                    return [$round->id => ($round->stage ? $round->stage->name . ' - ' : '') . $round->name];
                                });
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['tournament_id'])) {
                            $query->whereHas('season', fn($q) => $q->where('tournament_id', $data['tournament_id']));
                        }
                        if (!empty($data['round_id'])) {
                            $query->where('round_id', $data['round_id']);
                        }
                    }),

                // Фильтр "Только предстоящие"
                Tables\Filters\Filter::make('upcoming')
                    ->label('Только предстоящие')
                    ->query(fn (Builder $query) => $query->where('game_datetime', '>=', now()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('View')
                    ->url(fn (Game $record): string => GameResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated([20, 40, 60, 80, 100]);
    }
}