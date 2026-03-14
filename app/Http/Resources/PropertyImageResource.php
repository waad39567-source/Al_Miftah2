<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imagePath = $this->image_path;
        if ($imagePath && !str_starts_with($imagePath, 'http')) {
            $imagePath = asset($imagePath);
        }

        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'image_url' => $imagePath,
            'is_primary' => (bool) $this->is_primary,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
