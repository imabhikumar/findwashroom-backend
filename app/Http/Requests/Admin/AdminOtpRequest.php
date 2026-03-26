<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class AdminOtpRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'in:sms,email'],
            'identifier' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $channel = strtolower((string) $this->input('channel'));
            $identifier = (string) $this->input('identifier');

            if ($channel === 'sms' && ! preg_match('/^\d{10}$/', $identifier)) {
                $validator->errors()->add('identifier', 'For sms channel, identifier must be a 10-digit mobile number.');
            }

            if ($channel === 'email' && ! filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('identifier', 'For email channel, identifier must be a valid email.');
            }
        });
    }
}

