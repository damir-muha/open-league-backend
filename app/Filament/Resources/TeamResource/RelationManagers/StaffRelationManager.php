<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';
    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('role')->label('Должность в команде')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
                Tables\Columns\TextColumn::make('pivot.role')->label('Должность в команде'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->getSearchResultsUsing(function (string $search) {
                                $query = $this->getOwnerRecord()->getModel()->staff()->getRelated()
                                    ->where(function (Builder $query) use ($search) {
                                        // ОПТИМИЗАЦИЯ: поиск только по началу строки
                                        $query->where('first_name', 'like', "{$search}%")
                                              ->orWhere('last_name', 'like', "{$search}%");
                                    })
                                    ->limit(50);

                                return $query->get()->mapWithKeys(fn ($person) => [$person->id => $person->full_name]);
                            }),
                        Forms\Components\TextInput::make('role')->label('Должность')->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}