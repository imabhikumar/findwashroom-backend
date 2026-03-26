<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\ApiFormRequest;

class StoreComplaintRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'description' => ['required', 'string'],
            'evidence' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
