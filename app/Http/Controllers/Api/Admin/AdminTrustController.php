<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrustEventStoreRequest;
use App\Services\Admin\TrustService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTrustController extends Controller
{
    public function __construct(private readonly TrustService $service) {}

    public function events(Request $request): JsonResponse { return $this->paginated($this->service->events($request), 'Trust events fetched successfully.'); }
    public function users(Request $request): JsonResponse { return $this->paginated($this->service->users($request), 'Trust users fetched successfully.'); }
    public function show(int $id): JsonResponse { return $this->successResponse('Trust user fetched successfully.', $this->service->user($id)); }
    public function storeEvent(TrustEventStoreRequest $request): JsonResponse { return $this->successResponse('Trust event created successfully.', $this->service->storeEvent($request->validated(), $request), 201); }

    private function paginated($paginator, string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $paginator->items(), 'meta' => ['total' => $paginator->total(), 'page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'last_page' => $paginator->lastPage()]]);
    }
}