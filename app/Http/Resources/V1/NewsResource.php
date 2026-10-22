<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'imageUrl' => $this->getFirstMediaUrl('news'),
            'createdAt' => $this->created_at->toDateTimeString(),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}