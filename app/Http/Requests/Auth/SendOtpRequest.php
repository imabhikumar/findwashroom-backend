<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class SendOtpRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'digits:10'],
        ];
    }
}
