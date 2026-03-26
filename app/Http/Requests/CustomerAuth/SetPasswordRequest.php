<?php

namespace App\Http\Requests\CustomerAuth;

use App\Http\Requests\ApiFormRequest;

class SetPasswordRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
}

