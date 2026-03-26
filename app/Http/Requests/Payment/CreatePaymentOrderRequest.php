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
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ];
    }
}
