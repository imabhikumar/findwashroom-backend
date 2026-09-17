<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class SwitchRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:customer,owner,cleaner'],
        ];
    }
}
