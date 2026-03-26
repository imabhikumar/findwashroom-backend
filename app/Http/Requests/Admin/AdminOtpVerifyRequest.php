<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminOtpVerifyRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'in:sms,email'],
            'identifier' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ];
    }
}

