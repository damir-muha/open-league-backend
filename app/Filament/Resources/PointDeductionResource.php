<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointDeductionResource\Pages;
use App\Models\PointDeduction;
use App\Models\Season;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PointDeductionResource extends Resource
{
    protected static ?string $model = PointDeduction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';

    protected static ?string $navigationGroup = 'Tournaments structure';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('season_id')
                ->label('Season')
                // 1. Получаем только активные сезоны
                ->options(
                    Season::where('end_date', '>=', now())
                        ->with('tournament') // Загружаем турнир для отображения
                        ->get()
                        ->mapWithKeys(function ($season) {
                            // Форматируем название: "Турнир - Сезон"
                            return [$season->id => $season->tournament->name . ' - ' . $season->name];
                        })
                )
                ->searchable()
                ->required()
                ->reactive() // Делаем поле "реактивным"
                // Очищаем поле команды при смене сезона
                ->afterStateUpdated(fn (callable $set) => $set('team_id', null)),

            Forms\Components\Select::make('team_id')
                ->label('Team')
                // 2. Список команд зависит от выбранного сезона
                ->options(function (callable $get): Collection {
                    $seasonId = $get('season_id');
                    if (!$seasonId) {
                        return collect(); // Если сезон не выбран, список пуст
                    }
                    // Находим сезон и возвращаем список его команд
                    return Season::find($seasonId)->teams()->pluck('name', 'id');
                })
                ->searchable()
                ->required(),
                
            Forms\Components\TextInput::make('points')->numeric()->required(),
            Forms\Components\Textarea::make('reason')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('season.name')->sortable(),
            Tables\Columns\TextColumn::make('team.name')->sortable(),
            Tables\Columns\TextColumn::make('points')->sortable(),
            Tables\Columns\TextColumn::make('reason')->limit(50),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
    
    public static function getPages(): array {
        return ['index' => Pages\ListPointDeductions::route('/')];
    }    
}