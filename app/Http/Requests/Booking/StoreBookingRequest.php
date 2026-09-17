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
            'service_units' => ['required', 'array', 'min:1'],
            'service_units.*' => ['integer', 'distinct', 'exists:service_units,id'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'distinct', 'exists:products,id'],
            'booking_type' => ['required', Rule::in(['instant', 'scheduled', 'emergency', 'walk_in', 'group'])],
            'scheduled_at' => ['nullable', 'date', 'required_if:booking_type,scheduled', 'after:now'],
        ];
    }
}
