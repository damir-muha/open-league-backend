<?php

// --- Файл: app/Filament/Resources/PlayerResource.php ---
namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Filament\Resources\PlayerResource\RelationManagers; 
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Tournaments structure';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('registration_id')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('photo')
                    ->image()
                    ->directory('players-photos'),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\TextInput::make('citizenship')
                    ->maxLength(255),
                Forms\Components\Select::make('position')
                    ->options([
                        'Goalkeeper' => 'Goalkeeper',
                        'Defender' => 'Defender',
                        'Midfielder' => 'Midfielder',
                        'Forward' => 'Forward',
                    ]),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'injured' => 'Injured',
                        'suspended' => 'Suspended',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->circular(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'injured' => 'warning',
                        'suspended' => 'danger',
                    }),
            ])
            ->filters([
                //
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
            'duplicates' => Pages\ManageDuplicatePlayers::route('/duplicates'),
        ];
    }
}
