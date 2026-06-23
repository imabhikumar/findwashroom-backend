<?php

namespace App\Repositories;

use App\Models\Complaint;

class ComplaintRepository
{
    public function create(array $payload): Complaint
    {
        return Complaint::create($payload);
    }

    public function hasOpenByBookingAndRaisedBy(int $bookingId, int $raisedBy): bool
    {
        return Complaint::query()
            ->where('booking_id', $bookingId)
            ->where('raised_by', $raisedBy)
            ->where('status', 'pending')
            ->exists();
    }
}
