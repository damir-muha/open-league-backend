<?php
// --- Файл: app/Filament/Resources/EventResource.php ---

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        // Эта форма будет простой, т.к. основное управление идет через Relation Manager
        return $form
            ->schema([
                Forms\Components\Select::make('game_id')
                    ->relationship('game', 'id')
                    ->required(),
                Forms\Components\Select::make('player_id')
                    ->relationship('player', 'full_name')
                    ->required(),
                Forms\Components\TextInput::make('minute')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->options([
                        'goal' => 'Гол',
                        'yellow_card' => 'Желтая карточка',
                        'red_card' => 'Красная карточка',
                        'assist' => 'Голевая передача',
                        'substitution_in' => 'Выход на замену',
                        'substitution_out' => 'Уход с поля',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.id'),
                Tables\Columns\TextColumn::make('player.full_name'),
                Tables\Columns\TextColumn::make('minute'),
                Tables\Columns\TextColumn::make('type')->badge(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
