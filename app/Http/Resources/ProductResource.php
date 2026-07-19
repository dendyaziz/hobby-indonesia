<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'availability' => $this->availability,
            'price' => (int) $this->price,
            'discount_percentage' => $this->discount_percentage ? (float) $this->discount_percentage : null,
            'discounted_price' => $this->discounted_price ? (int) $this->discounted_price : null,
            'min_age' => $this->min_age,
            'min_player' => $this->min_player,
            'max_player' => $this->max_player,
            'playing_duration' => $this->playing_duration,
            'image_urls' => $this->getMedia('product-images')
                ->map(fn (Media $media): string => $media->getUrl())
                ->values()
                ->all(),
        ];
    }
}
