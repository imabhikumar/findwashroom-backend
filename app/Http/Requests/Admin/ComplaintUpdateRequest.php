<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class ComplaintUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:raised,evidence_submitted,under_review,mediation,resolved,closed,appealed'],
            'priority' => ['required', 'in:critical,high,medium,low'],
            'note' => ['nullable', 'string'],
        ];
    }
}