<?php

namespace App\Http\Requests\CustomerAuth;

use App\Http\Requests\ApiFormRequest;

class SetPinRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits_between:4,8'],
        ];
    }
}

