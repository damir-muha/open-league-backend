<?php

namespace App\Filament\Resources\SeasonResource\Pages;

use App\Filament\Resources\SeasonResource;
use App\Models\Game;
use App\Models\Standing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditSeason extends EditRecord
{
    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Кнопка пересчета таблицы
            Actions\Action::make('recalculate_standings')
                ->label('Recalculate Standings')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculate standings for this season?')
                ->modalDescription('This action will rebuild the standings table from scratch (including all historical round snapshots) based on all finished games. Manual penalty points will be preserved.')
                ->action(function () {
                    $this->recalculateStandings();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function recalculateStandings(): void
    {
        $season = $this->getRecord();

        app(\App\Services\StandingCalculationService::class)->recalculateForSeason($season);

        Notification::make()
            ->title('Standings successfully recalculated')
            ->success()
            ->send();
    }
}