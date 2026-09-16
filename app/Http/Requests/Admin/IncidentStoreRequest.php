<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class IncidentStoreRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['reported_by' => ['required', 'integer', 'exists:users,id'], 'category' => ['required', 'string', 'max:191'], 'severity' => ['required', 'in:low,medium,high,critical'], 'description' => ['required', 'string']]; }
}