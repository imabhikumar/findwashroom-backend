<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyController extends Controller
{
    public function __construct(private readonly PropertyService $propertyService)
    {
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->propertyService->create((int) auth()->id(), $request->validated());
        return $this->successResponse('Property created successfully.', $property);
    }

    public function myProperties(): JsonResponse
    {
        $properties = $this->propertyService->ownerList((int) auth()->id());
        return $this->successResponse('Owner properties fetched successfully.', $properties);
    }

    public function update(UpdatePropertyRequest $request, int $id): JsonResponse
    {
        try {
            $property = $this->propertyService->update((int) auth()->id(), $id, $request->validated());
            return $this->successResponse('Property updated successfully.', $property);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'gt:0', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'availability' => ['nullable', 'string', 'max:50'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'price_range' => ['nullable', 'string', 'max:50'],
            'badge' => ['nullable', 'string', 'max:100'],
            'women_safe' => ['nullable', 'boolean'],
            'family_safe' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:distance,price_asc,price_desc,rating,name'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $properties = $this->propertyService->publicList($filters);
        return $this->successResponse('Properties fetched successfully.', $properties);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $property = $this->propertyService->detail($id);
            return $this->successResponse('Property fetched successfully.', $property);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }
}
