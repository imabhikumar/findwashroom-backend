<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\PropertyRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly PropertyRepository $propertyRepository
    ) {
    }

    public function create(int $customerId, array $payload)
    {
        $property = $this->propertyRepository->findById((int) $payload['property_id']);
        if (! $property || ! $property->is_active) {
            throw new NotFoundHttpException('Property not found.');
        }

        if ((int) $property->owner_id === $customerId) {
            throw new BadRequestHttpException('Owner cannot book their own property.');
        }

        if ($this->bookingRepository->hasOpenBookingForCustomerProperty($customerId, (int) $property->id)) {
            throw new BadRequestHttpException('You already have an active or pending booking for this property.');
        }

        return $this->bookingRepository->create([
            'property_id' => $property->id,
            'customer_id' => $customerId,
            'status' => 'pending',
            'amount' => 0,
        ]);
    }

    public function start(int $customerId, int $bookingId)
    {
        return DB::transaction(function () use ($customerId, $bookingId) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'pending') {
                throw new BadRequestHttpException('Only pending bookings can be started.');
            }

            return $this->bookingRepository->update($booking, [
                'status' => 'active',
                'start_time' => Carbon::now(),
            ]);
        });
    }

    public function end(int $customerId, int $bookingId)
    {
        return DB::transaction(function () use ($customerId, $bookingId) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'active' || ! $booking->start_time) {
                throw new BadRequestHttpException('Booking is not active.');
            }

            $endTime = Carbon::now();
            $minutes = max($booking->start_time->diffInMinutes($endTime), 1);
            $pricePerUse = (float) $booking->property->price_per_use;
            $amount = round(($pricePerUse / 60) * $minutes, 2);

            return $this->bookingRepository->update($booking, [
                'status' => 'completed',
                'end_time' => $endTime,
                'amount' => $amount,
                'payment_status' => 'unpaid',
            ]);
        });
    }

    public function list(int $customerId)
    {
        return $this->bookingRepository->getByCustomer($customerId);
    }
}
