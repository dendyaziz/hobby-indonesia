<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonyResource extends JsonResource
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
            'subtitle' => $this->subtitle,
            'testimony' => $this->testimony,
            'image_url' => $this->getFirstMediaUrl('testimonies'),
        ];
    }
}
