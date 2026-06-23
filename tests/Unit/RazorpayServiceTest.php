<?php

namespace Tests\Unit;

use App\Services\RazorpayService;
use Tests\TestCase;

class RazorpayServiceTest extends TestCase
{
    public function test_it_verifies_valid_signature(): void
    {
        config()->set('services.razorpay.key_id', 'rzp_test_key');
        config()->set('services.razorpay.key_secret', 'secret123');

        $service = app(RazorpayService::class);

        $orderId = 'order_12345';
        $paymentId = 'pay_54321';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, 'secret123');

        $this->assertTrue($service->verifySignature($orderId, $paymentId, $signature));
    }

    public function test_it_rejects_invalid_signature(): void
    {
        config()->set('services.razorpay.key_id', 'rzp_test_key');
        config()->set('services.razorpay.key_secret', 'secret123');

        $service = app(RazorpayService::class);

        $this->assertFalse($service->verifySignature('order_12345', 'pay_54321', 'invalid-signature'));
    }

    public function test_it_converts_amount_to_subunits(): void
    {
        $service = app(RazorpayService::class);

        $this->assertSame(12345, $service->toSubunits(123.45));
    }
}