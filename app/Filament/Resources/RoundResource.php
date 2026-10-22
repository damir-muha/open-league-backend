<?php
// --- Файл: app/Filament/Resources/RoundResource.php ---

namespace App\Filament\Resources;

use App\Filament\Resources\RoundResource\Pages;
use App\Filament\Resources\RoundResource\RelationManagers;
use App\Models\Round;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoundResource extends Resource
{
    protected static ?string $model = Round::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('end_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.name')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GameRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRounds::route('/'),
            'create' => Pages\CreateRound::route('/create'),
            'edit' => Pages\EditRound::route('/{record}/edit'),
        ];
    }
}
