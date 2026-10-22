<?php

namespace App\Filament\Resources\GameLineupResource\Pages;

use App\Filament\Resources\GameLineupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameLineups extends ListRecords
{
    protected static string $resource = GameLineupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
