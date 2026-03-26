<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    public function create(array $payload): Booking
    {
        return Booking::create($payload);
    }

    public function findById(int $bookingId): ?Booking
    {
        return Booking::find($bookingId);
    }

    public function findByIdAndCustomer(int $bookingId, int $customerId): ?Booking
    {
        return Booking::query()
            ->where('id', $bookingId)
            ->where('customer_id', $customerId)
            ->first();
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Booking::query()
            ->with(['property', 'payment'])
            ->where('customer_id', $customerId)
            ->latest('id')
            ->get();
    }

    public function update(Booking $booking, array $payload): Booking
    {
        $booking->update($payload);
        return $booking->refresh();
    }
}
