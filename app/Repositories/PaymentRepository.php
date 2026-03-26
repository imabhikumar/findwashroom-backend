<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function create(array $payload): Payment
    {
        return Payment::create($payload);
    }

    public function findByBookingId(int $bookingId): ?Payment
    {
        return Payment::query()->where('booking_id', $bookingId)->latest('id')->first();
    }

    public function update(Payment $payment, array $payload): Payment
    {
        $payment->update($payload);
        return $payment->refresh();
    }
}
