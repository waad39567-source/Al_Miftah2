<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $regionText = null;
        if ($this->whenLoaded('region') && $this->region) {
            $parts = [];
            if ($this->region->parent) {
                if ($this->region->parent->parent) {
                    if ($this->region->parent->parent->parent) {
                        $parts[] = $this->region->parent->parent->parent->name;
                    }
                    $parts[] = $this->region->parent->parent->name;
                }
                $parts[] = $this->region->parent->name;
            }
            $parts[] = $this->region->name;
            $regionText = implode(' - ', $parts);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'type' => $this->type,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'location' => $this->location,
            'region' => $regionText,
            'images' => $this->whenLoaded('images', fn() => $this->images->isNotEmpty() 
                ? $this->images->map(fn($img) => url($img->image_path)) 
                : null),
        ];
    }
}
