<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class DisputeResolveRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['resolution' => ['required', 'string', 'min:10']]; }
}