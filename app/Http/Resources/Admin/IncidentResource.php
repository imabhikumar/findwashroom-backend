<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'reported_by' => $this->reporter ? ['id' => $this->reporter->id, 'name' => $this->reporter->name, 'role' => $this->reporter->role] : null, 'category' => $this->category, 'severity' => $this->severity, 'status' => $this->status, 'escalation_level' => $this->escalation_level, 'description' => $this->description, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at];
    }
}