<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyByTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'property_type' => $this['property_type'],
            'label' => $this['label'],
            'total' => $this['total'],
            'active' => $this['active'],
            'pending' => $this['pending'],
            'rented' => $this['rented'],
            'sold' => $this['sold'],
            'rejected' => $this['rejected'],
            'avg_price' => (float) $this['avg_price'],
            'min_price' => (float) $this['min_price'],
            'max_price' => (float) $this['max_price'],
        ];
    }
}
