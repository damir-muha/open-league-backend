<?php
// --- Файл: app/Filament/Resources/PersonResource/Pages/CreatePerson.php ---
namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    protected function afterCreate(): void
    {
        $userId = $this->data['user_id'];
        if ($userId) {
            $this->record->update(['user_id' => $userId]);
        }
    }
}
