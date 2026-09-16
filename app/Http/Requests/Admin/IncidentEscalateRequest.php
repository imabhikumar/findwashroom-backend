<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class IncidentEscalateRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['level' => ['required', 'integer', 'between:1,4'], 'reason' => ['required', 'string', 'min:5']]; }
}