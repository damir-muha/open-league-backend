<?php
// --- Файл: app/Filament/Resources/UserResource/Pages/CreateUser.php ---
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Person;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $personId = $this->data['person_id'];
        if ($personId) {
            $person = Person::find($personId);
            $person?->user()->associate($this->record)->save();
        }
    }
}
