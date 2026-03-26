<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\ComplaintRepository;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComplaintService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly ComplaintRepository $complaintRepository
    ) {
    }

    public function create(int $customerId, array $payload)
    {
        $booking = $this->bookingRepository->findByIdAndCustomer((int) $payload['booking_id'], $customerId);
        if (! $booking) {
            throw new NotFoundHttpException('Booking not found.');
        }
        if ($booking->status !== 'completed' || ! $booking->end_time) {
            throw new BadRequestHttpException('Complaint is allowed only after completed booking.');
        }
        if (Carbon::parse($booking->end_time)->diffInHours(Carbon::now()) > 24) {
            throw new BadRequestHttpException('Complaint window is closed (24 hours).');
        }

        return $this->complaintRepository->create([
            'booking_id' => $booking->id,
            'raised_by' => $customerId,
            'description' => $payload['description'],
            'evidence_image_path' => $payload['evidence_image_path'] ?? null,
            'status' => 'pending',
        ]);
    }
}
