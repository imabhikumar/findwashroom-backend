<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\ApiFormRequest;

class StoreBookingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
        ];
    }
}
