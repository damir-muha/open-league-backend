<?php

namespace App\Filament\Resources\GameLineupResource\Pages;

use App\Filament\Resources\GameLineupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGameLineup extends EditRecord
{
    protected static string $resource = GameLineupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
