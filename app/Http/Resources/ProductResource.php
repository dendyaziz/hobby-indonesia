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
            'description' => $this->description,
            'everything_you_need_to_know_description' => $this->everything_you_need_to_know_description,
            'everything_you_need_to_know_image_url' => $this->getFirstMediaUrl('everything-you-need-to-know-image') ?: null,
            'youtube' => $this->youtube,
            'playbook_url' => $this->playbook_url,
            'tiktok_videos' => $this->tiktok_videos ?? [],
            'brand' => $this->brand,
            'manufacture_country' => $this->manufacture_country,
            'publisher' => $this->publisher,
            'designer' => $this->designer,
            'artist' => $this->artist,
            'difficulty' => $this->difficulty,
            'themes' => $this->themes ?? [],
            'categories' => $this->relationLoaded('categories')
                ? CategoryResource::collection($this->categories)->resolve()
                : $this->whenLoaded('categories'),
            'characters' => $this->relationLoaded('characters')
                ? ProductCharacterResource::collection($this->characters)->resolve()
                : ProductCharacterResource::collection($this->characters()->orderBy('position')->get())->resolve(),
            'guides' => $this->relationLoaded('guides')
                ? ProductGuideResource::collection($this->guides)->resolve()
                : ProductGuideResource::collection($this->guides()->orderBy('position')->get())->resolve(),
            'image_urls' => $this->getMedia('product-images')
                ->map(fn (Media $media): string => $media->getUrl())
                ->values()
                ->all(),
        ];
    }
}
