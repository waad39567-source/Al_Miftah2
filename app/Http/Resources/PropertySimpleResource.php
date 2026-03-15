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
            $region = $this->region;
            if ($region->parent) {
                $parent1 = $region->parent;
                if ($parent1->parent) {
                    $parent2 = $parent1->parent;
                    if ($parent2->parent) {
                        $parts[] = $parent2->parent->name;
                    }
                    $parts[] = $parent2->name;
                }
                $parts[] = $parent1->name;
            }
            $parts[] = $region->name;
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
