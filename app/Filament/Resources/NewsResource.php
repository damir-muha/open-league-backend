<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\Player;
use App\Models\Team;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\MarkdownEditor;


class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'News';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic information')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Select::make('categories')
                            ->label('Categories')
                            ->multiple() 
                            ->relationship('categories', 'name')
                            ->preload()
                            ->required(),

                        MarkdownEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Main image')
                            ->collection('news')
                            ->image(),
                    ])->columns(2),

                Section::make('Is published')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published'),
                        DateTimePicker::make('published_at')
                            ->label('Published date'),
                    ]),

                Section::make('Related Entities')
                    ->schema([
                        Select::make('teams')
                            ->label('Teams')
                            ->multiple()
                            ->relationship('teams', 'name')
                            ->preload(),
                        Select::make('players')
                            ->label('Players')
                            ->multiple()
                            ->relationship(
                                name: 'players',
                                titleAttribute: 'last_name',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('last_name')->orderBy('first_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Player $record) => "{$record->last_name} {$record->first_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->label('Image')->collection('news'),
                TextColumn::make('title')->label('Title')->searchable()->sortable(),
                TextColumn::make('categories.name')->label('Categories')->sortable(),
                IconColumn::make('is_published')->label('Status')->boolean(),
                TextColumn::make('published_at')->label('Published date')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}