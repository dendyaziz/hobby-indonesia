<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'position' => $this->position,
            'type' => $this->type,
            'url' => $this->url,
            'image_url' => $this->getFirstMediaUrl('banners'),
        ];
    }
}
