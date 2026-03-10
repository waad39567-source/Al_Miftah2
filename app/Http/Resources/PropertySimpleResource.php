<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryImage = $this->images->firstWhere('is_primary') ?? $this->images->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => (float) $this->price,
            'type' => $this->type,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'region' => $this->whenLoaded('region', fn() => [
                'id' => $this->region->id,
                'name' => $this->region->name,
            ]),
            'image' => $primaryImage ? url($primaryImage->image_path) : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
