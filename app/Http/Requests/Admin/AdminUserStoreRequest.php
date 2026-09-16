<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminUserStoreRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:255', 'unique:users,mobile'],
            'role' => ['required', 'in:customer,owner,cleaner'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}