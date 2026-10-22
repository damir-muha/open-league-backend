<?php

namespace App\Filament\Resources\SeasonRosterResource\Pages;

use App\Filament\Resources\SeasonRosterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeasonRosters extends ListRecords
{
    protected static string $resource = SeasonRosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
