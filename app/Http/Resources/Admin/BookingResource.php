<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customer = $this->customer;
        $property = $this->property;
        $detail = $request->route('id') !== null;

        $data = [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
            ] : null,
            'property' => $property ? [
                'id' => $property->id,
                'name' => $property->name,
                'city' => $property->city,
            ] : null,
            'status' => $this->status,
            'booking_type' => $this->booking_type,
            'scheduled_at' => $this->scheduled_at,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'total_amount' => $this->total_amount ?? $this->amount,
            'created_at' => $this->created_at,
        ];

        if ($detail) {
            $data += [
                'service_units' => BookingServiceUnitResource::collection($this->serviceUnits),
                'products' => BookingProductResource::collection($this->products),
                'extensions' => BookingExtensionResource::collection($this->extensions),
                'events' => BookingEventResource::collection($this->events),
            ];
        }

        return $data;
    }
}