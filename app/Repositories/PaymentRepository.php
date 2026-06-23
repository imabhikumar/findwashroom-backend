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

    public function findPendingByBookingId(int $bookingId): ?Payment
    {
        return Payment::query()
            ->where('booking_id', $bookingId)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    public function findByGatewayOrderId(string $gatewayOrderId): ?Payment
    {
        return Payment::query()
            ->where('gateway_order_id', $gatewayOrderId)
            ->first();
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::query()
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function update(Payment $payment, array $payload): Payment
    {
        $payment->update($payload);
        return $payment->refresh();
    }
}
