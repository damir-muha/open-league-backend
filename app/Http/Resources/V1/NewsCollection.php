<?php

// --- Файл: app/Http/Resources/V1/NewsCollection.php (НОВЫЙ) ---

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NewsCollection extends ResourceCollection {

    public function toArray(Request $request): array { return parent::toArray($request); }
    
}