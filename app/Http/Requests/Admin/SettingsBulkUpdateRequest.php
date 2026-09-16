<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;

class SettingsBulkUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['settings' => ['required', 'array']]; }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            $whitelist = collect(config('admin_settings', []))
                ->flatMap(fn ($values) => array_keys($values))
                ->all();

            foreach (array_keys($this->input('settings', [])) as $key) {
                if (! in_array($key, $whitelist, true)) {
                    $validator->errors()->add(
                        "settings.{$key}",
                        'This setting key is not allowed.'
                    );
                }
            }
        });
    }
}