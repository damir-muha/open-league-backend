<?php
// --- Файл: app/Filament/Resources/GameResource/RelationManagers/EventRelationManager.php ---

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Models\GameLineup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EventRelationManager extends RelationManager
{
    protected static string $relationship = 'events';
    protected static ?string $recordTitleAttribute = 'type';

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('team_id')
                ->label('Team')
                ->options([
                    $this->getOwnerRecord()->home_team_id => $this->getOwnerRecord()->homeTeam->name,
                    $this->getOwnerRecord()->away_team_id => $this->getOwnerRecord()->awayTeam->name,
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('player_id', null)),

            Forms\Components\Select::make('player_id')
                ->label('Player')
                ->options(function (callable $get) {
                    $teamId = $get('team_id');
                    if (!$teamId) return [];
                    return \App\Models\GameLineup::where('game_id', $this->getOwnerRecord()->id)
                        ->where('team_id', $teamId)
                        ->get()
                        ->pluck('player.full_name', 'player.id');
                })
                ->searchable()
                ->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'goal' => '⚽ Goal',
                    'yellow_card' => '🟨 Yellow card',
                    'second_yellow_card' => '🟨+🟥 second YC', 
                    'red_card' => '🟥 Red Card',
                    'assist' => '🎯 Assist',
                    'substitution_in' => '↑ Substitution in',
                    'substitution_out' => '↓ Substitution out',
                ])
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('minute')
                ->label('Minute')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('suspension_duration')
                ->label('Period of disqualification (matches)')
                ->numeric()->minValue(1)
                ->visible(fn (\Filament\Forms\Get $get): bool => $get('type') === 'red_card')
                ->helperText('Specify how many matches the player will miss for a DIRECT red card.'),
            Forms\Components\Select::make('related_player_id')
                ->label('Associated player (for substitutions)')
                ->options($this->getAvailablePlayersForSubstitution())
                ->searchable()
                ->visible(fn (callable $get) => in_array($get('type'), ['substitution_in', 'substitution_out'])),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('minute')->label('Minute')->sortable(),
                Tables\Columns\TextColumn::make('player.full_name')->label('Player'),
                Tables\Columns\TextColumn::make('team.name')->label('Team'),
                Tables\Columns\TextColumn::make('type')->label('Event')->badge(),
            ])
            ->defaultSort('minute')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getAvailablePlayersForSubstitution(): array
    {
        return GameLineup::where('game_id', $this->getOwnerRecord()->id)
            ->get()
            ->pluck('player.full_name', 'player.id')
            ->all();
    }
}
