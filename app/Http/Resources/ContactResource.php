<?php

namespace App\Http\Resources;

use App\Helpers\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company_name' => $this->company_name,
            'telephone' => Phone::format($this->telephone),
            'email' => $this->email,
            'address' => $this->address,
        ];
    }
}
