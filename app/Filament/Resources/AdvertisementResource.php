<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Advertising';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([ 
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('ad_type')
                    ->label('Ad type')
                    ->options([
                        'banner' => 'Banner',
                        'adsense' => 'Google AdSense',
                    ])
                    ->required()
                    ->reactive() 
                    ->afterStateUpdated(fn (callable $set) => $set('image_url', null) & $set('target_url', null) & $set('code_snippet', null)), // Очищаем поля при смене типа

                Select::make('placement_area')
                    ->label('Placement area')
                    ->options([
                        'header' => 'Header',
                        'sidebar' => 'Sidebar',
                        'match_details_bottom' => 'Match page',
						'adsense-1' => 'Adsense-1',
						'adsense-2' => 'Adsense-2',
						'adsense-3' => 'Adsense-3',
                    ])
                    ->required(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->required(),

                TextInput::make('image_url')
                    ->label('URL image')
                    ->url()
                    ->visible(fn (callable $get) => $get('ad_type') === 'banner')
                    ->requiredIf('ad_type', 'banner'),

                TextInput::make('target_url')
                    ->label('URL for target')
                    ->url()
                    ->visible(fn (callable $get) => $get('ad_type') === 'banner')
                    ->requiredIf('ad_type', 'banner'),
                Textarea::make('code_snippet')
                    ->label('Код Google AdSense')
                    ->visible(fn (callable $get) => $get('ad_type') === 'adsense') 
                    ->requiredIf('ad_type', 'adsense')
                    ->columnSpanFull(),

                DatePicker::make('start_date')
                    ->label('Start date'),

                DatePicker::make('end_date')
                    ->label('End date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ad_type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'banner' => 'success',
                        'adsense' => 'warning',
                    }),
                TextColumn::make('placement_area')
                    ->label('Место')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),
                TextColumn::make('start_date')
                    ->label('Начало')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Окончание')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ad_type')
                    ->label('Тип')
                    ->options([
                        'banner' => 'Баннер',
                        'adsense' => 'Google AdSense',
                    ]),
                SelectFilter::make('placement_area')
                    ->label('Место размещения')
                    ->options([
                        'header' => 'Шапка сайта',
                        'sidebar' => 'Боковая колонка',
                        'match_details_bottom' => 'Под деталями матча',
                    ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
