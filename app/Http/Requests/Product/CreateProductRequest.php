// app/Http/Requests/Product/CreateProductRequest.php
<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'size' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:-1', // -1 = unlimited
            'availability' => 'required|in:available,limited,out_of_stock',
            'image_url' => 'nullable|url',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}