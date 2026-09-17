<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class RequestRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'customer' is granted automatically at registration, so the
            // only roles a user can actively request are owner or cleaner.
            'role' => ['required', 'in:owner,cleaner'],
        ];
    }
}
