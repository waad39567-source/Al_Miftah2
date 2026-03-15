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
            'type' => $this->type,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'location' => $this->location,
            'images' => $this->whenLoaded('images', fn() => $this->images->isNotEmpty() 
                ? $this->images->map(fn($img) => url($img->image_path)) 
                : null),
        ];
    }
}
