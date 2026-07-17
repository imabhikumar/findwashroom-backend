// app/Http/Resources/ProductResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'category' => $this->category?->name,
            'name' => $this->name,
            'brand' => $this->brand,
            'description' => $this->description,
            'size' => $this->size,
            'price' => number_format($this->price, 2),
            'discount_price' => $this->discount_price ? number_format($this->discount_price, 2) : null,
            'final_price' => number_format($this->final_price ?? $this->getFinalPrice(), 2),
            'stock_quantity' => $this->stock_quantity === -1 ? 'unlimited' : $this->stock_quantity,
            'availability' => $this->availability,
            'in_stock' => $this->in_stock ?? $this->isInStock(),
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}