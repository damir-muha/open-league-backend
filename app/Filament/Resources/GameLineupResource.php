<?php
// --- Файл: app/Filament/Resources/GameLineupResource.php ---

namespace App\Filament\Resources;

use App\Filament\Resources\GameLineupResource\Pages;
use App\Models\GameLineup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameLineupResource extends Resource
{
    protected static ?string $model = GameLineup::class;

    // Скрываем этот ресурс из главного меню навигации
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Форма здесь не так важна, т.к. управление идет через Relation Manager
                Forms\Components\Select::make('game_id')->relationship('game', 'id')->required(),
                Forms\Components\Select::make('player_id')->relationship('player', 'full_name')->required(),
                Forms\Components\Select::make('team_id')->relationship('team', 'name')->required(),
                Forms\Components\Select::make('status')->options(['starter' => 'Starter', 'substitute' => 'Substitute'])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.id'),
                Tables\Columns\TextColumn::make('player.full_name'),
                Tables\Columns\TextColumn::make('team.name'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGameLineups::route('/'),
            'create' => Pages\CreateGameLineup::route('/create'),
            'edit' => Pages\EditGameLineup::route('/{record}/edit'),
        ];
    }    
}
