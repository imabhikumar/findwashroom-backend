<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SosAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'user' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name, 'role' => $this->user->role, 'mobile' => $this->user->mobile] : null, 'booking' => $this->booking ? ['id' => $this->booking->id, 'booking_number' => $this->booking->booking_number] : null, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'status' => $this->status, 'acknowledged_by' => $this->acknowledged_by, 'acknowledged_at' => $this->acknowledged_at, 'resolved_at' => $this->resolved_at, 'created_at' => $this->created_at];
    }
}