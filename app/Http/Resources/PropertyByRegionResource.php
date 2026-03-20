<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyByRegionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'region_id' => $this['region_id'],
            'region_name' => $this['region_name'],
            'region_type' => $this['region_type'],
            'total_properties' => $this['total_properties'],
            'active' => $this['active'],
            'pending' => $this['pending'],
            'rented' => $this['rented'],
            'sold' => $this['sold'],
            'rejected' => $this['rejected'],
            'avg_price' => (float) $this['avg_price'],
        ];
    }
}
