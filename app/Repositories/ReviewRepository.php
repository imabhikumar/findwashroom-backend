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

    public function getPropertyAggregate(int $propertyId): array
    {
        $stats = Review::query()
            ->where('property_id', $propertyId)
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_reviews')
            ->first();

        return [
            'average_rating' => round((float) ($stats?->average_rating ?? 0), 2),
            'total_reviews' => (int) ($stats?->total_reviews ?? 0),
        ];
    }
}
