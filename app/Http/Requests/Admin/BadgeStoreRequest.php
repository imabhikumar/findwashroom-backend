<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class BadgeStoreRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:191'], 'description' => ['nullable', 'string'], 'badge_type' => ['required', 'string', 'max:191']]; }
}