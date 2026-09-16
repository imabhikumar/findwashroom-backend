<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource->toArray();
    }
}