// app/Services/ProductService.php
<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function getPropertyProducts(int $propertyId, bool $onlyAvailable = false): Collection
    {
        return $onlyAvailable
            ? $this->repository->getAvailableByProperty($propertyId)
            : $this->repository->getByProperty($propertyId);
    }

    public function getCategories(): Collection
    {
        return $this->repository->getCategories();
    }

    public function create(int $propertyId, array $data): Product
    {
        $data['property_id'] = $propertyId;
        return $this->repository->create($data);
    }

    public function update(int $productId, array $data): ?Product
    {
        return $this->repository->update($productId, $data);
    }

    public function updateStock(int $productId, int $quantity): ?Product
    {
        return $this->repository->updateStock($productId, $quantity);
    }

    public function getProductDetails(int $productId): ?Product
    {
        $product = $this->repository->findById($productId);
        if ($product) {
            $product->final_price = $product->getFinalPrice();
            $product->in_stock = $product->isInStock();
        }
        return $product;
    }

    public function deductStock(int $productId, int $quantity): bool
    {
        $product = $this->repository->findById($productId);
        if (!$product || !$product->isInStock()) {
            throw new \Exception('Product not available');
        }

        if ($product->stock_quantity !== -1 && $product->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $newQuantity = $product->stock_quantity === -1 ? -1 : $product->stock_quantity - $quantity;
        $this->repository->updateStock($productId, $newQuantity);
        return true;
    }
}