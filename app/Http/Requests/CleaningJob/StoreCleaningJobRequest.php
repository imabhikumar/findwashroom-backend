<?php

namespace App\Http\Requests\CleaningJob;

use App\Http\Requests\ApiFormRequest;

class StoreCleaningJobRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'price_offer' => ['required', 'numeric', 'min:0'],
        ];
    }
}
