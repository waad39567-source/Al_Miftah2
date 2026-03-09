<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'parent' => $this->whenLoaded('parent', fn() => new RegionResource($this->parent)),
            'children' => $this->whenLoaded('children', fn() => RegionResource::collection($this->children)),
            'properties' => $this->whenLoaded('properties', fn() => \App\Http\Resources\PropertyResource::collection($this->properties)),
        ];
    }
}
