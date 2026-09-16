<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentRefundRequest;
use App\Http\Resources\Admin\PaymentResource;
use App\Services\Admin\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate($request);

        return response()->json([
            'success' => true,
            'message' => 'Payments fetched successfully.',
            'data' => PaymentResource::collection($paginator->getCollection()),
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
            'Payment fetched successfully.',
            new PaymentResource($this->service->findOrFail($id))
        );
    }

    public function refund(PaymentRefundRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Payment refunded successfully.',
            new PaymentResource($this->service->refund($id, $request->validated(), $request))
        );
    }

    public function stats(): JsonResponse
    {
        return $this->successResponse(
            'Payment statistics fetched successfully.',
            $this->service->stats()
        );
    }
}