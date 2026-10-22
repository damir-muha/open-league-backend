<?php
// --- Файл: app/Filament/Resources/CategoryResource.php (НОВЫЙ) ---
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'News';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('name')->searchable(),])
            ->actions([Tables\Actions\EditAction::make(),])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(),]),]);
    }
    
    public static function getPages(): array { return ['index' => Pages\ListCategories::route('/'), 'create' => Pages\CreateCategory::route('/create'), 'edit' => Pages\EditCategory::route('/{record}/edit'),]; }
}