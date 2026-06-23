<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayService
{
    public function createOrder(string $receipt, float $amount): array
    {
        $amountInSubunits = $this->toSubunits($amount);
        if ($amountInSubunits <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->acceptJson()
            ->retry(2, 200)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountInSubunits,
                'currency' => $this->currency(),
                'receipt' => $receipt,
                'payment_capture' => 1,
            ]);

        if ($response->failed()) {
            report(new RuntimeException('Razorpay order creation failed: '.$response->body()));
            throw new RuntimeException('Unable to create payment order right now.');
        }

        $payload = $response->json();
        if (! is_array($payload) || empty($payload['id'])) {
            throw new RuntimeException('Invalid Razorpay order response.');
        }

        return $payload;
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret());

        return hash_equals($expectedSignature, $signature);
    }

    public function toSubunits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public function currency(): string
    {
        return strtoupper((string) config('services.razorpay.currency', 'INR'));
    }

    public function keyId(): string
    {
        $keyId = (string) config('services.razorpay.key_id');
        if ($keyId === '') {
            throw new RuntimeException('Razorpay key id is not configured.');
        }

        return $keyId;
    }

    private function keySecret(): string
    {
        $keySecret = (string) config('services.razorpay.key_secret');
        if ($keySecret === '') {
            throw new RuntimeException('Razorpay key secret is not configured.');
        }

        return $keySecret;
    }
}