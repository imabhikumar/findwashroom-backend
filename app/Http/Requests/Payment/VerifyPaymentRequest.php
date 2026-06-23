<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\ApiFormRequest;

class VerifyPaymentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'min:1', 'exists:bookings,id'],
            'order_id' => ['required', 'string', 'max:255', 'regex:/^order_[A-Za-z0-9]+$/'],
            'payment_id' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'signature' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]+$/i'],
        ];
    }
}
