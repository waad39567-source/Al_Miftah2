<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentActivityPropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'owner' => $this->owner?->name,
            'type' => $this->type,
            'status' => $this->status,
            'price' => (float) $this->price,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
