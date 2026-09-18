<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\ApiFormRequest;

class CreatePaymentOrderRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'min:1', 'exists:bookings,id'],
            'order_id' => ['nullable', 'string', 'max:255', 'regex:/^order_[A-Za-z0-9]+$/'],
            'payment_id' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-]+$/'],
        ];
    }
}
