<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class BadgeAssignRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['badge_id' => ['required', 'integer', 'exists:badges,id'], 'user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:property_id'], 'property_id' => ['nullable', 'integer', 'exists:properties,id', 'required_without:user_id']]; }
}