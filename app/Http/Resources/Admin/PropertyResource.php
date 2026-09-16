<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $owner = $this->relationLoaded('owner') ? $this->owner : null;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'owner' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'mobile' => $owner->mobile,
            ] : null,
            'city' => $this->city,
            'state' => $this->state,
            'status' => $this->status,
            'property_type' => $this->property_type,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];

        if ($request->route('id') !== null) {
            $data += [
                'description' => $this->description,
                'address' => $this->address,
                'country' => $this->country,
                'pincode' => $this->pincode,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'updated_at' => $this->updated_at?->toDateTimeString(),
                'deleted_at' => $this->deleted_at?->toDateTimeString(),
                'service_units' => $this->whenLoaded('serviceUnits'),
                'amenities' => $this->when(isset($this->amenities), $this->amenities),
                'safety_features' => $this->when(isset($this->safety_features), $this->safety_features),
            ];
        }

        return $data;
    }
}