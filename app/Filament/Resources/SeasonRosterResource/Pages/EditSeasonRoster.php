<?php

namespace App\Filament\Resources\SeasonRosterResource\Pages;

use App\Filament\Resources\SeasonRosterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeasonRoster extends EditRecord
{
    protected static string $resource = SeasonRosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
