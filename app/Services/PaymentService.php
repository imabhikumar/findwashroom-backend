<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly PaymentRepository $paymentRepository
    ) {
    }

    public function createOrder(int $customerId, int $bookingId): array
    {
        $booking = $this->bookingRepository->findByIdAndCustomer($bookingId, $customerId);
        if (! $booking) {
            throw new NotFoundHttpException('Booking not found.');
        }
        if ($booking->amount <= 0) {
            throw new BadRequestHttpException('Booking amount is not payable yet.');
        }

        $orderId = 'order_'.Str::uuid();

        $payment = $this->paymentRepository->create([
            'booking_id' => $booking->id,
            'payment_gateway' => 'razorpay',
            'transaction_id' => $orderId,
            'amount' => $booking->amount,
            'platform_commission' => round($booking->amount * 0.15, 2),
            'owner_amount' => round($booking->amount * 0.85, 2),
            'status' => 'pending',
        ]);

        return [
            'order_id' => $orderId,
            'amount' => $payment->amount,
            'currency' => 'INR',
            'booking_id' => $booking->id,
        ];
    }

    public function verifyPayment(int $customerId, int $bookingId, string $paymentId): array
    {
        $booking = $this->bookingRepository->findByIdAndCustomer($bookingId, $customerId);
        if (! $booking) {
            throw new NotFoundHttpException('Booking not found.');
        }

        $payment = $this->paymentRepository->findByBookingId($bookingId);
        if (! $payment) {
            throw new NotFoundHttpException('Payment record not found.');
        }

        $this->paymentRepository->update($payment, [
            'status' => 'success',
            'transaction_id' => $paymentId,
        ]);

        $this->bookingRepository->update($booking, ['payment_status' => 'paid']);

        return [
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
        ];
    }
}
