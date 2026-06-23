<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly RazorpayService $razorpayService
    ) {
    }

    public function createOrder(int $customerId, int $bookingId): array
    {
        return Cache::lock("payments:create-order:{$bookingId}", 10)->block(5, function () use ($customerId, $bookingId) {
            $booking = $this->bookingRepository->findByIdAndCustomer($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }
            if ($booking->status !== 'completed') {
                throw new BadRequestHttpException('Payment can be created only for completed bookings.');
            }
            if (($booking->payment_status ?? 'unpaid') === 'paid') {
                throw new BadRequestHttpException('Booking is already paid.');
            }
            if ($booking->amount <= 0) {
                throw new BadRequestHttpException('Booking amount is not payable yet.');
            }

            $existingPending = $this->paymentRepository->findPendingByBookingId($booking->id);
            if ($existingPending && $existingPending->gateway_order_id) {
                return $this->buildOrderResponse($booking->id, (float) $existingPending->amount, (string) $existingPending->gateway_order_id);
            }

            $gatewayOrder = $this->razorpayService->createOrder(
                receipt: 'booking_'.$booking->id.'_'.Str::lower(Str::random(10)),
                amount: (float) $booking->amount,
            );

            $payload = [
                'booking_id' => $booking->id,
                'payment_gateway' => 'razorpay',
                'gateway_order_id' => $gatewayOrder['id'],
                'amount' => $booking->amount,
                'platform_commission' => round($booking->amount * 0.15, 2),
                'owner_amount' => round($booking->amount * 0.85, 2),
                'status' => 'pending',
            ];

            if ($existingPending) {
                $this->paymentRepository->update($existingPending, $payload);
            } else {
                $this->paymentRepository->create($payload);
            }

            return $this->buildOrderResponse($booking->id, (float) $booking->amount, (string) $gatewayOrder['id']);
        });
    }

    public function verifyPayment(int $customerId, int $bookingId, string $orderId, string $paymentId, string $signature): array
    {
        return DB::transaction(function () use ($customerId, $bookingId, $orderId, $paymentId, $signature) {
            $booking = $this->bookingRepository->findByIdAndCustomerForUpdate($bookingId, $customerId);
            if (! $booking) {
                throw new NotFoundHttpException('Booking not found.');
            }

            if (($booking->payment_status ?? 'unpaid') === 'paid') {
                return [
                    'booking_id' => $booking->id,
                    'payment_status' => 'paid',
                ];
            }

            $duplicateGatewayPayment = $this->paymentRepository->findByTransactionId($paymentId);
            if ($duplicateGatewayPayment && (int) $duplicateGatewayPayment->booking_id !== $bookingId) {
                throw new BadRequestHttpException('Payment transaction already linked to another booking.');
            }

            $payment = $this->paymentRepository->findPendingByBookingId($bookingId);
            if (! $payment) {
                throw new NotFoundHttpException('Pending payment record not found.');
            }
            if (! $payment->gateway_order_id || $payment->gateway_order_id !== $orderId) {
                throw new BadRequestHttpException('Payment order mismatch.');
            }
            if (! $this->razorpayService->verifySignature($orderId, $paymentId, $signature)) {
                throw new BadRequestHttpException('Invalid payment signature.');
            }

            $this->paymentRepository->update($payment, [
                'status' => 'success',
                'transaction_id' => $paymentId,
                'gateway_payment_id' => $paymentId,
                'gateway_signature' => $signature,
            ]);

            $this->bookingRepository->update($booking, ['payment_status' => 'paid']);

            return [
                'booking_id' => $booking->id,
                'payment_status' => 'paid',
                'order_id' => $orderId,
                'payment_id' => $paymentId,
            ];
        });
    }

    private function buildOrderResponse(int $bookingId, float $amount, string $orderId): array
    {
        return [
            'order_id' => $orderId,
            'amount' => $this->razorpayService->toSubunits($amount),
            'display_amount' => round($amount, 2),
            'currency' => $this->razorpayService->currency(),
            'booking_id' => $bookingId,
            'key' => $this->razorpayService->keyId(),
        ];
    }
}
