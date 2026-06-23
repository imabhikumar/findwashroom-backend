<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Payment\CreatePaymentOrderRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function createOrder(CreatePaymentOrderRequest $request)
    {
        try {
            $data = $this->paymentService->createOrder(
                (int) auth()->id(),
                (int) $request->validated('booking_id')
            );
            return $this->successResponse('Payment order created successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function verify(VerifyPaymentRequest $request)
    {
        try {
            $data = $this->paymentService->verifyPayment(
                (int) auth()->id(),
                (int) $request->validated('booking_id'),
                $request->validated('order_id'),
                $request->validated('payment_id'),
                $request->validated('signature')
            );
            return $this->successResponse('Payment verified successfully.', $data);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
