<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class PaymentRefundRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'min:5'],
        ];
    }
}