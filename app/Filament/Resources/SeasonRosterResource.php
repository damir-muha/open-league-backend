<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonRosterResource\Pages;
use App\Filament\Resources\SeasonRosterResource\RelationManagers;
use App\Models\Season;
use App\Models\SeasonRoster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class SeasonRosterResource extends Resource
{
    protected static ?string $model = SeasonRoster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Tournaments structure';

    protected static ?int $navigationSort = 5; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- Вот это изменение ---
                Forms\Components\Select::make('season_id')
                    ->label('Season')
                    ->options(
                        Season::with('tournament')->get()->mapWithKeys(function ($season) {
                            return [$season->id => $season->tournament->name . ' - ' . $season->name];
                        })->all()
                    )
                    ->searchable()
                    ->required(),
                // --- Конец изменения ---
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('season.tournament.name')
                    ->label('Tournamenent')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('season.name')
                    ->label('Season')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('players_count')->counts('players')
                    ->label('Players in the season'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament')
                    ->label('Tournament')
                    ->relationship('season.tournament', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship(
                        name: 'season',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->with('tournament')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tournament->name} - {$record->name}")
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('active_season')
                    ->label('Active seasons')
                    ->query(fn (Builder $query): Builder => $query->whereHas('season', fn($q) => $q->where('end_date', '>=', now())))
                    ->default(),

                Tables\Filters\Filter::make('inactive_season')
                    ->label('Completed seasons')
                    ->query(fn (Builder $query): Builder => $query->whereHas('season', fn($q) => $q->where('end_date', '<', now()))),
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
            RelationManagers\PlayerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasonRosters::route('/'),
            'create' => Pages\CreateSeasonRoster::route('/create'),
            'edit' => Pages\EditSeasonRoster::route('/{record}/edit'),
        ];
    }
}
