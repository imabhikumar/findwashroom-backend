<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SafetyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'reported_by' => $this->reporter ? ['id' => $this->reporter->id, 'name' => $this->reporter->name, 'role' => $this->reporter->role] : null, 'against_user' => $this->againstUser ? ['id' => $this->againstUser->id, 'name' => $this->againstUser->name] : null, 'against_property' => $this->againstProperty ? ['id' => $this->againstProperty->id, 'name' => $this->againstProperty->name] : null, 'category' => $this->category, 'description' => $this->description, 'created_at' => $this->created_at];
    }
}