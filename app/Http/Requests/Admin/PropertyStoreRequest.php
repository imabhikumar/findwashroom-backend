<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class PropertyStoreRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['required', 'integer', 'exists:users,id,role,owner'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'property_type' => ['required', 'string', 'max:100'],
            'status' => ['sometimes', 'in:pending,approved,rejected,suspended,banned'],
        ];
    }
}