<?php
// --- Файл: app/Filament/Resources/PersonResource/Pages/EditPerson.php ---
namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use App\Models\Person;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_id'] = $this->record->user_id;
        return $data;
    }

    protected function afterSave(): void
    {
        $userId = $this->data['user_id'];
        $person = $this->getRecord();
        if ($userId) {
            Person::where('user_id', $userId)->where('id', '!=', $person->id)->update(['user_id' => null]);
        }
        $person->update(['user_id' => $userId]);
    }
}
