<?php
// --- Файл: app/Filament/Resources/UserResource/Pages/EditUser.php ---
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Person;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['person_id'] = $this->record->person?->id;
        return $data;
    }

    protected function afterSave(): void
    {
        $personId = $this->data['person_id'];
        $user = $this->getRecord();
        Person::where('user_id', $user->id)->where('id', '!=', $personId)->update(['user_id' => null]);
        if ($personId) {
            $person = Person::find($personId);
            $person?->user()->associate($user)->save();
        }
    }
}