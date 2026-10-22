<?php
// --- Файл: app/Http/Controllers/Api/V1/PersonController.php  ---
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PersonCollection;
use App\Http\Resources\V1\PersonResource;
use App\Models\Person;

class PersonController extends Controller
{
    public function index()
    {
        return new PersonCollection(Person::paginate());
    }

    public function show(Person $person)
    {
        return new PersonResource($person->loadMissing('teams'));
    }
}