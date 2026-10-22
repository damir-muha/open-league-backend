<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonResource\Pages;
use App\Filament\Resources\SeasonResource\RelationManagers;
use App\Models\Season;
use App\Models\Team;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Tournaments structure';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Besic information')
                        ->schema([
                            Forms\Components\Toggle::make('is_current')
                                ->label('Current season')
                                ->helperText('Only one season can be current at a time.')
                                ->onColor('success'),
                            Forms\Components\Select::make('standings_display_mode')
                                ->label('Standings Display Mode (Mobile App)')
                                ->options([
                                    'single_table' => 'Single Table (No Tabs)',
                                    'by_stage' => 'Divide by Stages (Tabs)',
                                ])
                                ->default('single_table')
                                ->required()
                                ->helperText('Choose how to display standings in the mobile app.'),
                            Forms\Components\Select::make('tournament_id')
                                ->label('Tournament')
                                ->options(fn () => \App\Models\Tournament::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('start_date')
                                ->required(),
                            Forms\Components\DatePicker::make('end_date')
                                ->required(),
                            Forms\Components\RichEditor::make('regulations')
                                ->label('Regulations')
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('description')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Wizard\Step::make('Rules and Limits')
                        ->schema([
                            Forms\Components\Select::make('format_type')
                                ->options([
                                    'league' => 'League (circular system)',
                                    'group_and_playoff' => 'Group stage and playoffs',
                                    'knockout' => 'Playoff (knockout)',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('roster_min_players')
                                ->label('Min. players in the season')
                                ->required()
                                ->numeric()
                                ->default(15),
                            Forms\Components\TextInput::make('roster_max_players')
                                ->label('Max players in the season')
                                ->required()
                                ->numeric()
                                ->default(25),
                            Forms\Components\TextInput::make('players_on_field')
                                ->label('Players on the field')
                                ->required()
                                ->numeric()
                                ->default(11),
                            Forms\Components\TextInput::make('substitutes_on_bench')
                                ->label('Reserve players')
                                ->required()
                                ->numeric()
                                ->default(7),
                            Forms\Components\Repeater::make('ranking_rules')
                                ->label('Table sorting rules (by priority)')
                                ->columns(2)
                                ->schema([
                            Forms\Components\Select::make('column')
                                ->label('Parameter')
                                ->options([
                                    'points' => 'Point',
                                    'goal_difference' => 'Goal difference',
                                    'goals_for' => 'Goals Scored',
                                    'wins' => 'Won',
                                    'head_to_head' => 'Head to head',
                                ])
                                ->required(),
                            Forms\Components\Select::make('direction')
                                ->label('Direction')
                                ->options([
                                    'desc' => 'Desc',
                                    'asc' => 'Asc',
                                ])
                                ->required()
                                ->default('desc'),
                                ])
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('yellow_cards_for_suspension')
                                ->label('Number of YC for disqualification')
                                ->numeric()->required()->default(3)->minValue(1)
                                ->helperText('Specify after which yellow card the player will receive a 1-match disqualification.')
                                ->columnSpanFull(), 
                        ]),
                    Forms\Components\Wizard\Step::make('Dates and deadlines')
                        ->schema([
                            Forms\Components\DateTimePicker::make('roster_deadline')
                                ->label('Roster dedline')
                                ->required(),
                            Forms\Components\DateTimePicker::make('transfer_window_start')
                                ->label('Transfer window start')
                                ->required(),
                            Forms\Components\DateTimePicker::make('transfer_window_end')
                                ->label('Transfer window end')
                                ->required(),
                        ]),
                    Forms\Components\Wizard\Step::make('Teams')
                        ->schema([
                            Forms\Components\Select::make('teams')
                                ->multiple()
                                ->relationship('teams', 'name')
                                ->preload() 
                                ->searchable()
                                ->label('Select teams to participate in the season'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Tournament')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('format_type')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament_id')
                    ->relationship('tournament', 'name')
                    ->label('Tournament')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('active')
                    ->label('Active seasons')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '>=', now()))
                    ->default(),

                Tables\Filters\Filter::make('inactive')
                    ->label('Completed seasons')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StageRelationManager::class, 
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'edit' => Pages\EditSeason::route('/{record}/edit'),
        ];
    }
}