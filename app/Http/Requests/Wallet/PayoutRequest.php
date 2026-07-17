// app/Http/Requests/Wallet/PayoutRequest.php
<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class PayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1|max:100000',
            'method' => 'required|in:bank_transfer,upi,razorpay',
            'account_details' => 'required|array',
            'account_details.account_number' => 'required_if:method,bank_transfer|string',
            'account_details.ifsc' => 'required_if:method,bank_transfer|string',
            'account_details.upi_id' => 'required_if:method,upi|string',
        ];
    }
}
