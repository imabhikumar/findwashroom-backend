<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class BookingCancelRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5']];
    }
}