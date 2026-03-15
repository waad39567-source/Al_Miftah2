<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isApproved = $this->status === 'approved';
        
        $isSender = $request->user() && $request->user()->id === $this->user_id;
        
        if ($isSender) {
            $data = [
                'id' => $this->id,
                'property_id' => $this->property_id,
                'status' => $this->status,
                'message' => $this->message,
                'created_at' => $this->created_at->toDateTimeString(),
                'property' => $this->whenLoaded('property', fn() => [
                    'id' => $this->property->id,
                    'title' => $this->property->title,
                    'price' => $this->property->price,
                    'type' => $this->property->type,
                    'property_type' => $this->property->property_type,
                    'location' => $this->property->location,
                    'status' => $this->property->status,
                ]),
            ];
            
            if ($this->status === 'rejected') {
                $data['rejection_reason'] = $this->rejection_reason;
            }
            
            if ($this->status === 'approved' && $this->whenLoaded('owner')) {
                $data['owner'] = [
                    'name' => $this->owner->name,
                    'phone' => $this->owner->phone,
                ];
            }
            
            return $data;
        }
        
        $isOwner = $request->user() && $request->user()->id === $this->owner_id;
        
        if ($isOwner && $isApproved) {
            return [
                'id' => $this->id,
                'property_id' => $this->property_id,
                'status' => $this->status,
                'message' => $this->message,
                'created_at' => $this->created_at->toDateTimeString(),
                'user' => $this->whenLoaded('user', fn() => [
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                ]),
                'property' => $this->whenLoaded('property', fn() => [
                    'id' => $this->property->id,
                    'title' => $this->property->title,
                    'price' => $this->property->price,
                    'type' => $this->property->type,
                    'property_type' => $this->property->property_type,
                    'location' => $this->property->location,
                ]),
            ];
        }

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
            'owner' => null,
        ];
    }
}
