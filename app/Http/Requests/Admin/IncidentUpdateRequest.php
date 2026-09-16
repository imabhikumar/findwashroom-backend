<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class IncidentUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['status' => ['required', 'in:open,investigating,resolved,closed'], 'severity' => ['required', 'in:low,medium,high,critical'], 'description' => ['required', 'string']]; }
}