<?php
// app/Http/Resources/BadgeResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'description' => $this->description,
            'type' => $this->type,
            'awarded_at' => $this->pivot?->awarded_at ?? $this->awarded_at,
        ];
    }
}