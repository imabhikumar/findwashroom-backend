<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->create((int) auth()->id(), $request->validated());
            return $this->successResponse('Booking created successfully.', $booking);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function start(int $id)
    {
        try {
            $booking = $this->bookingService->start((int) auth()->id(), $id);
            return $this->successResponse('Booking started successfully.', $booking);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function end(int $id)
    {
        try {
            $booking = $this->bookingService->end((int) auth()->id(), $id);
            return $this->successResponse('Booking ended successfully.', $booking);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function index()
    {
        $bookings = $this->bookingService->list((int) auth()->id());
        return $this->successResponse('Bookings fetched successfully.', $bookings);
    }
}
