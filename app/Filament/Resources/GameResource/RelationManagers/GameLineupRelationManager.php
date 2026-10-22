<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Models\Player;
use App\Models\SeasonRoster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

class GameLineupRelationManager extends RelationManager
{
    protected static string $relationship = 'lineups';
    protected static ?string $recordTitleAttribute = 'player_id';

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
                        if (!$teamId) {
                            return []; 
                        }

                        $game = $this->getOwnerRecord();
                        $seasonId = $game->season_id;

                        $roster = SeasonRoster::where('season_id', $seasonId)
                            ->where('team_id', $teamId)
                            ->first();

                        if (!$roster) {
                            return []; 
                        }

                        return $roster->players->pluck('full_name', 'id');
                    })
                    ->searchable()
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'starter' => 'В старте',
                        'substitute' => 'В запасе',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player.full_name')->label('Player'),
                Tables\Columns\TextColumn::make('team.name')->label('Team'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
