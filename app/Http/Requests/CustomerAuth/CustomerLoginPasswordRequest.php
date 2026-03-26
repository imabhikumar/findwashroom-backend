<?php

namespace App\Http\Requests\CustomerAuth;

use App\Http\Requests\ApiFormRequest;

class CustomerLoginPasswordRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }
}

