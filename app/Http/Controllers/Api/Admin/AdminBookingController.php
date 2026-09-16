<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingCancelRequest;
use App\Http\Requests\Admin\BookingExtendRequest;
use App\Http\Resources\Admin\BookingResource;
use App\Services\Admin\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function __construct(private readonly BookingService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate($request);

        return response()->json([
            'success' => true,
            'message' => 'Bookings fetched successfully.',
            'data' => BookingResource::collection($paginator->getCollection()),
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
            'Booking fetched successfully.',
            new BookingResource($this->service->findOrFail($id))
        );
    }

    public function cancel(BookingCancelRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Booking cancelled successfully.',
            new BookingResource($this->service->cancel($id, $request->validated('reason'), $request))
        );
    }

    public function forceComplete(BookingCancelRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Booking completed successfully.',
            new BookingResource($this->service->forceComplete($id, $request->validated('reason'), $request))
        );
    }

    public function extend(BookingExtendRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'Booking extended successfully.',
            new BookingResource($this->service->extend($id, $request->validated(), $request))
        );
    }

    public function stats(): JsonResponse
    {
        return $this->successResponse('Booking statistics fetched successfully.', $this->service->stats());
    }
}