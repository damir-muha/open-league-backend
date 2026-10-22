<?php

namespace App\Filament\Resources\SeasonRosterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;   

class PlayerRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    protected static ?string $recordTitleAttribute = 'full_name'; // Лучше искать по полному имени

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birth_date')
                    ->required(), // Делаем дату обязательной для точной проверки
                Forms\Components\TextInput::make('citizenship')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pivot.jersey_number')->label('Номер'),
                Tables\Columns\TextColumn::make('full_name')->label('Имя Фамилия')->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('birth_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // === ЗАЩИТА ОТ ДУБЛЕЙ ПЕРЕД СОЗДАНИЕМ ===
                    ->before(function (Tables\Actions\CreateAction $action, array $data) {
                        $exists = Player::where('first_name', $data['first_name'])
                            ->where('last_name', $data['last_name'])
                            ->where('birth_date', $data['birth_date'])
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->warning()
                                ->title('Дубликат!')
                                ->body('Такой игрок уже существует в базе. Найдите его через поиск ("Прикрепить"), вместо создания нового.')
                                ->persistent()
                                ->send();
                            
                            $action->halt(); // Останавливаем создание
                        }
                    })
                    // ==========================================
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $player = Player::create($data);
                        $seasonId = $livewire->getOwnerRecord()->season_id;
                        $livewire->getRelationship()->attach($player->id, ['season_id' => $seasonId]);
                        return $player;
                    }),

                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect() // Подгружаем список для быстрого поиска
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable(['first_name', 'last_name', 'registration_id'])
                            ->label('Поиск игрока'),
                        TextInput::make('jersey_number')
                            ->label('Номер игрока')
                            ->numeric()
                            ->required()
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        // (Здесь твой старый код проверки лимитов...)
                        $seasonId = $livewire->getOwnerRecord()->season_id;
                        $livewire->getRelationship()->attach($data['recordId'], [
                            'season_id' => $seasonId, 
                            'jersey_number' => $data['jersey_number']
                        ]);
                    }),     
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Сменить номер')
                    ->form([
                        TextInput::make('jersey_number')->label('Новый номер')->numeric()->required(),
                    ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}