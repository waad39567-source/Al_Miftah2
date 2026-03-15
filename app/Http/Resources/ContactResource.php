<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isApproved = $this->status === 'approved';
        $isOwner = $request->user() && $this->property && $request->user()->id === $this->property->owner_id;

        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'message' => $this->message,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at->toDateTimeString(),
            'property' => $this->whenLoaded('property', fn() => new PropertyResource($this->property)),
            'owner' => ($isApproved || $isOwner) && $this->whenLoaded('owner', fn() => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'phone' => $isApproved ? $this->owner->phone : null,
            ]),
        ];
    }
}