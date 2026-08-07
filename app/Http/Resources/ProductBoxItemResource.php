<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBoxItemResource extends JsonResource
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
            'label' => $this->label,
            'icon_name' => $this->icon_name,
            'icon_url' => $this->icon_name === 'custom' ? $this->getFirstMediaUrl('box-item-custom-icon') ?: null : null,
            'position' => (int) $this->position,
        ];
    }
}
