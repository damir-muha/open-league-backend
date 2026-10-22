<?php

namespace App\Filament\Resources\SeasonResource\RelationManagers;

use App\Filament\Resources\StageResource; // <-- Добавлено
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // <-- Добавлено

class StageRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'league' => 'League / Regular Season',
                        'group' => 'Group Stage',
                        'knockout' => 'Knockout / Playoffs',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    // --- Вот это изменение ---
                    ->url(fn (Model $record): string => StageResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
