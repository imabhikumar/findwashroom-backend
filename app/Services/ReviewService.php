<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReviewService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly ReviewRepository $reviewRepository
    ) {
    }

    public function create(int $customerId, array $payload)
    {
        return DB::transaction(function () use ($customerId, $payload) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate((int) $payload['booking_id'], $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'completed') {
                throw new BadRequestHttpException('Review allowed only after booking completion.');
            }
            if ($this->reviewRepository->findByBookingId($booking->id)) {
                throw new BadRequestHttpException('Review already submitted for this booking.');
            }

            $review = $this->reviewRepository->create([
                'booking_id' => $booking->id,
                'reviewer_id' => $customerId,
                'property_id' => $booking->property_id,
                'rating' => $payload['rating'],
                'comment' => $payload['comment'] ?? null,
            ]);

            $aggregate = $this->reviewRepository->getPropertyAggregate((int) $booking->property_id);
            $booking->property->update($aggregate);

            return $review;
        });
    }
}
