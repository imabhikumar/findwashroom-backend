<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComplaintResolveRequest;
use App\Http\Requests\Admin\ComplaintUpdateRequest;
use App\Http\Resources\Admin\ComplaintResource;
use App\Services\Admin\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminComplaintController extends Controller
{
    public function __construct(private readonly ComplaintService $service) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate($request);
        return response()->json([
            'success' => true,
            'message' => 'Complaints fetched successfully.',
            'data' => ComplaintResource::collection($paginator->getCollection()),
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
        return $this->successResponse('Complaint fetched successfully.', new ComplaintResource($this->service->findOrFail($id)));
    }

    public function update(ComplaintUpdateRequest $request, int $id): JsonResponse
    {
        return $this->successResponse('Complaint updated successfully.', new ComplaintResource($this->service->update($id, $request->validated(), $request)));
    }

    public function resolve(ComplaintResolveRequest $request, int $id): JsonResponse
    {
        return $this->successResponse('Complaint resolved successfully.', new ComplaintResource($this->service->resolve($id, $request->validated('resolution'), $request)));
    }

    public function escalate(Request $request, int $id): JsonResponse
    {
        return $this->successResponse('Complaint escalated successfully.', new ComplaintResource($this->service->escalate($id, $request)));
    }

    public function stats(): JsonResponse
    {
        return $this->successResponse('Complaint statistics fetched successfully.', $this->service->stats());
    }
}