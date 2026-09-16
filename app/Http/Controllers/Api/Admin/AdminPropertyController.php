<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyReasonRequest;
use App\Http\Requests\Admin\PropertyStoreRequest;
use App\Http\Requests\Admin\PropertyUpdateRequest;
use App\Http\Resources\Admin\PropertyResource;
use App\Services\Admin\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPropertyController extends Controller
{
    public function __construct(private readonly PropertyService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate($request);

        return response()->json([
            'success' => true,
            'message' => 'Properties fetched successfully.',
            'data' => PropertyResource::collection($paginator->getCollection()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return $this->successResponse(
            'Property fetched successfully.',
            new PropertyResource($this->service->findOrFail($id))
        );
    }

    public function store(PropertyStoreRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Property created successfully.',
            new PropertyResource($this->service->create($request->validated(), $request)),
            201
        );
    }

    public function update(PropertyUpdateRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Property updated successfully.',
            new PropertyResource($this->service->update($id, $request->validated(), $request))
        );
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return $this->status($request, $id, 'approved', null);
    }

    public function reject(PropertyReasonRequest $request, int $id): JsonResponse
    {
        return $this->status($request, $id, 'rejected', $request->validated('reason'));
    }

    public function suspend(PropertyReasonRequest $request, int $id): JsonResponse
    {
        return $this->status($request, $id, 'suspended', $request->validated('reason'));
    }

    public function ban(PropertyReasonRequest $request, int $id): JsonResponse
    {
        return $this->status($request, $id, 'banned', $request->validated('reason'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id, $request);

        return $this->successResponse('Property deleted successfully.');
    }

    private function status(Request $request, int $id, string $status, ?string $reason): JsonResponse
    {
        return $this->successResponse(
            "Property {$status} successfully.",
            new PropertyResource($this->service->changeStatus($id, $status, $reason, $request))
        );
    }
}