<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class PropertyUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'state' => ['sometimes', 'required', 'string', 'max:100'],
            'country' => ['sometimes', 'required', 'string', 'max:100'],
            'pincode' => ['sometimes', 'required', 'string', 'max:20'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'property_type' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'in:pending,approved,rejected,suspended,banned'],
        ];
    }
}