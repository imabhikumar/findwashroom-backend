<?php

namespace App\Http\Requests\CustomerAuth;

use App\Http\Requests\ApiFormRequest;

class CustomerLoginPinRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'digits_between:4,8'],
        ];
    }
}

