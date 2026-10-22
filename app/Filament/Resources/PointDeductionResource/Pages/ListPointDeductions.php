<?php

namespace App\Filament\Resources\PointDeductionResource\Pages;

use App\Filament\Resources\PointDeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPointDeductions extends ListRecords
{
    protected static string $resource = PointDeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
