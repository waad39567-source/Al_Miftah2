<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'properties_by_region' => $this['properties_by_region'] ?? [],
            'properties_by_type' => $this['properties_by_type'] ?? [],
            'properties_by_status' => $this['properties_by_status'] ?? [],
            'users_by_role' => $this['users_by_role'] ?? [],
        ];
    }
}
