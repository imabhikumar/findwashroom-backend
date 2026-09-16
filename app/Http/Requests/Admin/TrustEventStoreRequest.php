<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class TrustEventStoreRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['user_id' => ['required', 'integer', 'exists:users,id'], 'event_type' => ['required', Rule::in(config('trust.event_types', []))], 'score_change' => ['required', 'integer', 'between:' . config('trust.score_min', -100) . ',' . config('trust.score_max', 100)], 'reason' => ['required', 'string', 'min:5']]; }
}