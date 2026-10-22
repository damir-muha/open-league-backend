<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ad_type' => $this->ad_type,
            'title' => $this->title,
            'code' => $this->code_snippet,
            'position' => $this->placement_area,
            'targetUrl' => $this->target_url,
            'image' => $this->image_url,
            'active' => $this->is_active,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ];
    }
}
