<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentActivityContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }
        
        return [
            'id' => $this->id,
            'user_name' => $this->user?->name ?? $this->name,
            'property_title' => $this->property?->title,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
