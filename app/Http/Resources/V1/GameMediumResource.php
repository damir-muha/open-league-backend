<?php

// =================================================================
//      Шаг 1: Создание API-ресурсов для медиа
// =================================================================

/*
 * 1.1. Создай ресурс для одного медиа-файла
 * Выполни в терминале:
 */
 // php artisan make:resource V1/GameMediumResource

/*
 * 1.2. Вставь этот код в созданный файл:
 * app/Http/Resources/V1/GameMediumResource.php
 */
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GameMediumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->media_type,
            'title' => $this->title,
            'url' => $this->media_type === 'photo' ? Storage::url($this->url) : $this->url,
        ];
    }
}