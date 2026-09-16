<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class SosResolveRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['note' => ['nullable', 'string']]; }
}