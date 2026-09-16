<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class BookingExtendRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}