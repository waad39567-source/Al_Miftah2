<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'location' => $this->location,
        ];
    }
}
