<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class RefundRuleRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['rule_name' => ['required', 'string', 'max:255'], 'conditions' => ['nullable', 'array'], 'refund_type' => ['required', 'string', 'max:50'], 'value' => ['required', 'numeric', 'min:0'], 'priority' => ['sometimes', 'integer', 'min:0'], 'is_active' => ['sometimes', 'boolean']]; }
}