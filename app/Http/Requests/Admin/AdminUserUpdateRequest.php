<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminUserUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', "unique:users,email,{$id}"],
            'mobile' => ['sometimes', 'required', 'string', 'max:255', "unique:users,mobile,{$id}"],
            'status' => ['sometimes', 'required', 'in:active,suspended,banned'],
        ];
    }
}