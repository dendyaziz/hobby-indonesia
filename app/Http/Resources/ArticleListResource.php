<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ArticleListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => Str::of(strip_tags((string) $this->content))->squish()->limit(160)->toString(),
            'read_duration' => $this->read_duration,
            'image_url' => $this->getFirstMediaUrl('featured_images'),
        ];
    }
}
