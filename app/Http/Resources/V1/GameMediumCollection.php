<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GameMediumCollection extends ResourceCollection
{
    // Указываем, что для каждого элемента коллекции нужно использовать GameMediumResource
    public $collects = GameMediumResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}