<?php

namespace App\Http\Requests\CleaningJob;

use App\Http\Requests\ApiFormRequest;

class UploadCleaningProofRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof' => ['required', 'image', 'max:5120'],
        ];
    }
}
