<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BadgeAssignRequest;
use App\Http\Requests\Admin\BadgeStoreRequest;
use App\Services\Admin\TrustService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBadgeController extends Controller
{
    public function __construct(private readonly TrustService $service) {}
    public function index(): JsonResponse { return $this->successResponse('Badges fetched successfully.', $this->service->badges()); }
    public function store(BadgeStoreRequest $request): JsonResponse { return $this->successResponse('Badge created successfully.', $this->service->createBadge($request->validated(), $request), 201); }
    public function update(BadgeStoreRequest $request, int $id): JsonResponse { return $this->successResponse('Badge updated successfully.', $this->service->updateBadge($id, $request->validated(), $request)); }
    public function destroy(Request $request, int $id): JsonResponse { $this->service->deleteBadge($id, $request); return $this->successResponse('Badge deleted successfully.'); }
    public function assign(BadgeAssignRequest $request): JsonResponse { return $this->successResponse('Badge assigned successfully.', $this->service->assignBadge($request->validated(), $request)); }
    public function revoke(BadgeAssignRequest $request): JsonResponse { return $this->successResponse('Badge revoked successfully.', $this->service->revokeBadge($request->validated(), $request)); }
}