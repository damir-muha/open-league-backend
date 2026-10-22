<?php
// --- Файл: app/Filament/Resources/PersonResource.php ---
namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Filament Shield';

    protected static ?int $navigationSort = 3; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Связанный пользователь системы')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable()
                    ->unique(ignoreRecord: true)
                    ->dehydrated(false),
                Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
                Forms\Components\Select::make('role')->options(['coach' => 'Coach', 'referee' => 'Referee', 'manager' => 'Manager', 'staff' => 'Staff'])->required(),
                Forms\Components\FileUpload::make('photo')->image()->directory('people-photos'),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\TextInput::make('citizenship')->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->circular(),
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Пользователь системы'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
