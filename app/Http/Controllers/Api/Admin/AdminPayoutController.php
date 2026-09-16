<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PayoutRejectRequest;
use App\Http\Resources\Admin\PayoutResource;
use App\Services\Admin\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function __construct(private readonly PaymentService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->payouts($request);

        return response()->json([
            'success' => true,
            'message' => 'Payouts fetched successfully.',
            'data' => PayoutResource::collection($paginator->getCollection()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Payout approved successfully.',
            new PayoutResource($this->service->approvePayout($id, $request))
        );
    }

    public function reject(PayoutRejectRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Payout rejected successfully.',
            new PayoutResource($this->service->rejectPayout($id, $request->validated('reason'), $request))
        );
    }
}