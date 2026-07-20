<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'service_type' => $this->serviceType?->name,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'current_occupancy' => $this->current_occupancy ?? $this->getCurrentOccupancy(),
            'has_capacity' => $this->has_capacity ?? $this->hasCapacity(),
            'default_duration_minutes' => $this->default_duration_minutes,
            'price' => number_format($this->price, 2),
            'pricing_model' => $this->pricing_model,
            'status' => $this->status,
            'operating_hours' => $this->operating_hours,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}