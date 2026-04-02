<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'type' => $this->type,
            'property_type' => $this->property_type,
            'area' => $this->area,
            'region_id' => $this->region_id,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'is_active' => $this->is_active,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'region' => $this->whenLoaded('region', fn() => new RegionResource($this->region)),
            'images' => $this->whenLoaded('images', fn() => \App\Http\Resources\PropertyImageResource::collection($this->images)),
        ];
    }
}
