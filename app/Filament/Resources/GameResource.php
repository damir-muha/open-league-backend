<?php
// --- Файл: app/Filament/Resources/GameResource.php ---

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Models\Game;
use App\Models\Person;
use App\Models\Season;
use App\Models\Team;
use App\Models\Venue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\GameResource\RelationManagers\GameMediaRelationManager;
use Filament\Tables\Actions\Action; 

class GameResource extends Resource
{
    protected static ?string $model = Game::class;
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tournament & Stage')
                    ->description('Specify the stage and round for this game')
                    ->schema([
                        Forms\Components\Select::make('season_id')
                            ->label('Season')
                            ->options(
                                Season::with('tournament')->get()->mapWithKeys(function ($season) {
                                    return [$season->id => $season->tournament->name . ' - ' . $season->name];
                                })->all()
                            )
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('home_team_id', null) & $set('away_team_id', null) & $set('stage_id', null) & $set('group_id', null) & $set('round_id', null)),

                        Forms\Components\Select::make('stage_id')
                            ->label('Stage')
                            ->options(function (callable $get) {
                                $seasonId = $get('season_id');
                                if (!$seasonId) return [];
                                return \App\Models\Stage::where('season_id', $seasonId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('group_id', null) & $set('round_id', null)),
                            
                        Forms\Components\Select::make('group_id')
                            ->label('Group')
                            ->options(function (callable $get) {
                                $stageId = $get('stage_id');
                                if (!$stageId) return [];
                                return \App\Models\Group::where('stage_id', $stageId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->nullable(),
                            
                        Forms\Components\Select::make('round_id')
                            ->label('Round')
                            ->options(function (callable $get) {
                                $stageId = $get('stage_id');
                                if (!$stageId) {
                                    $seasonId = $get('season_id');
                                    if (!$seasonId) return [];
                                    return \App\Models\Round::whereHas('stage', function($q) use ($seasonId) {
                                        $q->where('season_id', $seasonId);
                                    })->with('stage')->get()->mapWithKeys(function ($round) {
                                        return [$round->id => $round->stage->name . ' - ' . $round->name];
                                    });
                                }
                                return \App\Models\Round::where('stage_id', $stageId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Teams & Result')
                    ->schema([
                        Forms\Components\Select::make('home_team_id')
                            ->label('Home Team')
                            ->options(function (callable $get) {
                                $seasonId = $get('season_id');
                                if ($seasonId) {
                                    return Season::find($seasonId)->teams()->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('away_team_id')
                            ->label('Away Team')
                            ->options(function (callable $get) {
                                $seasonId = $get('season_id');
                                if ($seasonId) {
                                    return Season::find($seasonId)->teams()->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('home_score')
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('away_score')
                            ->numeric()
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('home_penalty_score')
                            ->numeric()
                            ->label('Home Penalty Score')
                            ->nullable()
                            ->visible(function (callable $get, ?Model $record) {
                                if (!$record || !$record->stage) return false;
                                $isPlayoff = $record->stage->type === 'playoff' || $record->season->format_type === 'knockout';
                                $hasScores = $get('home_score') !== null && $get('home_score') !== '' && $get('away_score') !== null && $get('away_score') !== '';
                                return $isPlayoff && $hasScores && (int) $get('home_score') === (int) $get('away_score');
                            }),
                        Forms\Components\TextInput::make('away_penalty_score')
                            ->numeric()
                            ->label('Away Penalty Score')
                            ->nullable()
                            ->visible(function (callable $get, ?Model $record) {
                                if (!$record || !$record->stage) return false;
                                $isPlayoff = $record->stage->type === 'playoff' || $record->season->format_type === 'knockout';
                                $hasScores = $get('home_score') !== null && $get('home_score') !== '' && $get('away_score') !== null && $get('away_score') !== '';
                                return $isPlayoff && $hasScores && (int) $get('home_score') === (int) $get('away_score');
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Match Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('game_datetime')
                            ->required(),
                        Forms\Components\Select::make('venue_id')
                            ->label('Venue')
                            ->options(Venue::all()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'finished' => 'Finished',
                                'postponed' => 'Postponed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('referees')
                            ->label('Referees')
                            ->multiple()
                            ->relationship(
                                name: 'referees',
                                modifyQueryUsing: fn (Builder $query) => $query->where('people.role', 'referee')->orderBy('first_name')->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->full_name)
                            ->preload()
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('Playoff Bracket')
                    ->description('Where the winner and loser advance to')
                    ->schema([
                        Forms\Components\Select::make('winner_to_game_id')
                            ->label('Winner advances to Game')
                            ->options(function (callable $get, ?Model $record) {
                                if (!$record) return [];
                                return \App\Models\Game::where('stage_id', $get('stage_id'))
                                    ->where('id', '!=', $record->id)
                                    ->get()->mapWithKeys(fn($g) => [$g->id => "Game {$g->id} - {$g->homeTeam?->name} vs {$g->awayTeam?->name}"]);
                            })->searchable()->nullable(),
                        Forms\Components\Select::make('loser_to_game_id')
                            ->label('Loser advances to Game')
                            ->options(function (callable $get, ?Model $record) {
                                if (!$record) return [];
                                return \App\Models\Game::where('stage_id', $get('stage_id'))
                                    ->where('id', '!=', $record->id)
                                    ->get()->mapWithKeys(fn($g) => [$g->id => "Game {$g->id} - {$g->homeTeam?->name} vs {$g->awayTeam?->name}"]);
                            })->searchable()->nullable(),
                    ])->columns(2)
                    ->visible(function (callable $get) {
                        $stageId = $get('stage_id');
                        if (!$stageId) return false;
                        $stage = \App\Models\Stage::find($stageId);
                        return $stage && $stage->type === 'playoff';
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.name')->label('Stage')->sortable(),
                Tables\Columns\TextColumn::make('group.name')->label('Group')->sortable(),
                Tables\Columns\TextColumn::make('round.name')->label('Round')->sortable(),
                Tables\Columns\TextColumn::make('homeTeam.name'),
                Tables\Columns\TextColumn::make('awayTeam.name'),
                Tables\Columns\TextColumn::make('game_datetime')->dateTime(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ]);
            

        
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GameLineupRelationManager::class,
            RelationManagers\EventRelationManager::class,
            GameMediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}

