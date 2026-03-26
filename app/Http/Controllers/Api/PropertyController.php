<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyController extends Controller
{
    public function __construct(private readonly PropertyService $propertyService)
    {
    }

    public function store(StorePropertyRequest $request)
    {
        $property = $this->propertyService->create((int) auth()->id(), $request->validated());
        return $this->successResponse('Property created successfully.', $property);
    }

    public function myProperties()
    {
        $properties = $this->propertyService->ownerList((int) auth()->id());
        return $this->successResponse('Owner properties fetched successfully.', $properties);
    }

    public function update(UpdatePropertyRequest $request, int $id)
    {
        try {
            $property = $this->propertyService->update((int) auth()->id(), $id, $request->validated());
            return $this->successResponse('Property updated successfully.', $property);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function index()
    {
        $properties = $this->propertyService->publicList();
        return $this->successResponse('Properties fetched successfully.', $properties);
    }

    public function show(int $id)
    {
        try {
            $property = $this->propertyService->detail($id);
            return $this->successResponse('Property fetched successfully.', $property);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }
}