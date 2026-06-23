<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => [
                'required',
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ];
    }
}
