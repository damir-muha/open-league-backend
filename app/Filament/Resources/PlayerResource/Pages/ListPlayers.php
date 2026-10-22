<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlayers extends ListRecords
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manageDuplicates')
                ->label('Manage Duplicates')
                ->color('warning')
                ->icon('heroicon-o-users')
                ->url(fn () => PlayerResource::getUrl('duplicates')),
            Actions\CreateAction::make(),
        ];
    }
}
