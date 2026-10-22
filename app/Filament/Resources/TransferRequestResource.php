<?php
// --- Файл: app/Filament/Resources/TransferRequestResource.php ---

namespace App\Filament\Resources;

use App\Filament\Resources\TransferRequestResource\Pages;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonRoster;
use App\Models\Team;
use App\Models\TransferRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransferRequestResource extends Resource
{
    protected static ?string $model = TransferRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Tournaments structure';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('season_id')
                    ->label('Season')
                    ->options(
                        Season::with('tournament')->get()->mapWithKeys(function ($season) {
                            return [$season->id => $season->tournament->name . ' - ' . $season->name];
                        })->all()
                    )
                    ->searchable()
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('from_team_id')
                    ->label('From team')
                    ->options(fn (callable $get) => Season::find($get('season_id'))?->teams()->pluck('name', 'id') ?? [])
                    ->searchable()
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('player_id')
                    ->label('Player')
                    ->options(function (callable $get) {
                        $roster = SeasonRoster::where('season_id', $get('season_id'))
                            ->where('team_id', $get('from_team_id'))
                            ->first();
                        return $roster ? $roster->players->pluck('full_name', 'id') : [];
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('to_team_id')
                    ->label('To team')
                    ->options(fn (callable $get) => Season::find($get('season_id'))?->teams()->pluck('name', 'id') ?? [])
                    ->searchable()
                    ->required(),
                
                // Скрываем поля, которые управляются автоматически
                Forms\Components\Hidden::make('requested_by_manager_id')->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player.full_name')->label('Player')->searchable(),
                Tables\Columns\TextColumn::make('fromTeam.name')->label('From team'),
                Tables\Columns\TextColumn::make('toTeam.name')->label('To team'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Request date')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (TransferRequest $record) => self::handleApproval($record))
                    ->visible(fn (TransferRequest $record) => $record->status === 'pending'),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (TransferRequest $record) => $record->update(['status' => 'rejected']))
                    ->visible(fn (TransferRequest $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferRequests::route('/'),
            'create' => Pages\CreateTransferRequest::route('/create'),
            'edit' => Pages\EditTransferRequest::route('/{record}/edit'),
        ];
    }

    public static function handleApproval(TransferRequest $record): void
    {
        // Находим заявки старой и новой команды
        $fromRoster = SeasonRoster::where('season_id', $record->season_id)
            ->where('team_id', $record->from_team_id)->first();
            
        $toRoster = SeasonRoster::firstOrCreate(
            ['season_id' => $record->season_id, 'team_id' => $record->to_team_id]
        );

        if (!$fromRoster) {
            Notification::make()->title('Ошибка!')->body('Не найдена заявка исходной команды.')->danger()->send();
            return;
        }

        // Перемещаем игрока
        $fromRoster->players()->detach($record->player_id);
        $toRoster->players()->attach($record->player_id, ['season_id' => $record->season_id]);

        // Обновляем статус трансфера
        $record->update([
            'status' => 'approved',
            'approved_by_manager_id' => auth()->id(),
        ]);
        
        Notification::make()->title('Трансфер одобрен!')->body('Игрок успешно переведен в новую команду.')->success()->send();
    }
}
