<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use App\Filament\Resources\GroupResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Form $form): Form
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
                    ->options(function (RelationManager $livewire) {
                        $stage = $livewire->getOwnerRecord();
                        return $stage ? $stage->season->teams->pluck('name', 'id') : [];
                    }),
                Forms\Components\Fieldset::make('Points Inheritance')
                    ->schema([
                        Forms\Components\Select::make('inherits_from_group_id')
                            ->label('Inherit points from group (Stage 1)')
                            ->options(function (RelationManager $livewire, ?Model $record) {
                                $stage = $livewire->getOwnerRecord();
                                if (!$stage) return [];
                                
                                return \App\Models\Group::whereHas('stage', function($q) use ($stage) {
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('teams_count')->counts('teams')->label('Teams Count'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record): string => GroupResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
