<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function getByProperty(int $propertyId): Collection
    {
        return Product::with(['category'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->get();
    }

    public function getAvailableByProperty(int $propertyId): Collection
    {
        return Product::with(['category'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->where('availability', '!=', 'out_of_stock')
            ->get();
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['property', 'category'])->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->findById($id);
        if ($product) {
            $product->update($data);
        }
        return $product;
    }

    public function updateStock(int $id, int $quantity): ?Product
    {
        $product = $this->findById($id);
        if ($product) {
            $product->update(['stock_quantity' => $quantity]);
            // Update availability based on stock
            if ($quantity <= 0) {
                $product->update(['availability' => 'out_of_stock']);
            } elseif ($quantity <= 5) {
                $product->update(['availability' => 'limited']);
            } else {
                $product->update(['availability' => 'available']);
            }
        }
        return $product;
    }

    public function getCategories(): Collection
    {
        return ProductCategory::where('is_active', true)->get();
    }
}