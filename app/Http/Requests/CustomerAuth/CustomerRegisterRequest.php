<?php

namespace App\Http\Requests\CustomerAuth;

use App\Http\Requests\ApiFormRequest;

class CustomerRegisterRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['nullable', 'digits:10', 'unique:users,mobile'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'pin' => ['nullable', 'digits_between:4,8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $mobile = $this->input('mobile');

        if (($email === null || $email === '') && ($mobile === null || $mobile === '')) {
            $this->merge(['_missing_identifier' => true]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('_missing_identifier')) {
                $validator->errors()->add('email', 'Email or mobile is required.');
                $validator->errors()->add('mobile', 'Email or mobile is required.');
            }
        });
    }
}

