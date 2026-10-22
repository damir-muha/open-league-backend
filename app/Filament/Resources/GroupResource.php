<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use App\Models\Season;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('teams')
                    ->multiple()
                    ->relationship('teams', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Teams in Group')
                    // Эта логика позволяет выбирать только из команд, заявленных на сезон
                    ->options(function (callable $get, ?Group $record) {
                        $stageId = $get('stage_id');
                        if (!$stageId) {
                            if ($record && $record->stage) {
                                $stageId = $record->stage_id;
                            } else {
                                return [];
                            }
                        }
                        $stage = \App\Models\Stage::find($stageId);
                        return $stage ? $stage->season->teams->pluck('name', 'id') : [];
                    }),
                Forms\Components\Fieldset::make('Points Inheritance')
                    ->schema([
                        Forms\Components\Select::make('inherits_from_group_id')
                            ->label('Inherit points from group (Stage 1)')
                            ->options(function (callable $get, ?Group $record) {
                                $stageId = $get('stage_id');
                                if (!$stageId) {
                                    if ($record && $record->stage) {
                                        $stageId = $record->stage_id;
                                    } else {
                                        return [];
                                    }
                                }
                                $stage = \App\Models\Stage::find($stageId);
                                if (!$stage) return [];
                                
                                return Group::whereHas('stage', function($q) use ($stage) {
                                    $q->where('season_id', $stage->season_id);
                                })
                                ->where('id', '!=', $record?->id) // нельзя наследовать от себя
                                ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('inherit_mode')
                            ->label('Inheritance Rule')
                            ->options([
                                'none' => 'Do not inherit (Start from 0)',
                                'full' => 'Inherit 100% (As is)',
                                'halved_up' => 'Divide points by 2 (Round UP)',
                                'halved_down' => 'Divide points by 2 (Round DOWN)',
                            ])
                            ->default('none')
                            ->required(),
                    ])
                    ->columns(1),
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required()
                    ->hidden(), // Скрываем, т.к. создаем из этапа
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}