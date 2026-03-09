<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'message' => $this->message,
            'created_at' => $this->created_at->toDateTimeString(),
            'property' => $this->whenLoaded('property', fn() => new PropertyResource($this->property)),
            'owner' => $this->whenLoaded('owner', fn() => new UserResource($this->owner)),
        ];
    }
}
