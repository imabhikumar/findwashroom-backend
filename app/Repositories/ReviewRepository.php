<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{
    public function findByBookingId(int $bookingId): ?Review
    {
        return Review::query()->where('booking_id', $bookingId)->first();
    }

    public function create(array $payload): Review
    {
        return Review::create($payload);
    }
}
