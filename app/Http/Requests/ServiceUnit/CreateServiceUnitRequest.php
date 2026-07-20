<?php

namespace App\Http\Requests\ServiceUnit;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'default_duration_minutes' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'pricing_model' => 'required|in:fixed,dynamic,negotiable',
            'status' => 'required|in:available,limited,busy,cleaning,closed,emergency',
            'operating_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}