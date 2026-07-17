// app/Http/Controllers/Api/ProductController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpdateStockRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(int $propertyId)
    {
        $products = $this->productService->getPropertyProducts($propertyId);
        return $this->successResponse('Products fetched.', ProductResource::collection($products));
    }

    public function available(int $propertyId)
    {
        $products = $this->productService->getPropertyProducts($propertyId, true);
        return $this->successResponse('Available products fetched.', ProductResource::collection($products));
    }

    public function categories()
    {
        $categories = $this->productService->getCategories();
        return $this->successResponse('Product categories fetched.', $categories);
    }

    public function store(CreateProductRequest $request)
    {
        $product = $this->productService->create(
            (int) $request->validated('property_id'),
            $request->validated()
        );
        return $this->successResponse('Product created.', new ProductResource($product));
    }

    public function show(int $id)
    {
        $product = $this->productService->getProductDetails($id);
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        return $this->successResponse('Product details.', new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productService->update($id, $request->validated());
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        return $this->successResponse('Product updated.', new ProductResource($product));
    }

    public function updateStock(UpdateStockRequest $request, int $id)
    {
        $product = $this->productService->updateStock($id, (int) $request->validated('quantity'));
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        return $this->successResponse('Product stock updated.', new ProductResource($product));
    }
}