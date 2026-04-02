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
                if ($parent1 && $parent1->parent) {
                    $parent2 = $parent1->parent;
                    if ($parent2 && $parent2->parent) {
                        $parts[] = $parent2->parent->name;
                    }
                    if ($parent2->name) {
                        $parts[] = $parent2->name;
                    }
                }
                if ($parent1->name) {
                    $parts[] = $parent1->name;
                }
            }
            if ($region->name) {
                $parts[] = $region->name;
            }
            $regionText = implode(' - ', array_filter($parts));
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'type' => $this->type,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'location' => $this->location,
            'region' => $regionText,
            'status' => $this->status,
            'images' => $this->whenLoaded('images', fn() => $this->images->isNotEmpty() 
                ? $this->images->map(fn($img) => config('app.url') . '/api/images/' . $img->image_path) 
                : null),
        ];
    }
}
