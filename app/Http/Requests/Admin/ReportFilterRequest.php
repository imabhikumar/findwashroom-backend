<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'group_by' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'type' => ['sometimes', Rule::in(['bookings', 'revenue', 'complaints'])],
        ];
    }
}