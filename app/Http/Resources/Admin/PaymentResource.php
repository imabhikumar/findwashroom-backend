<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->booking;
        $customer = $booking?->customer;

        return [
            'id' => $this->id,
            'booking' => $booking ? [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
            ] : null,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ] : null,
            'gateway' => $this->gateway ?? $this->payment_gateway,
            'gateway_transaction_id' => $this->gateway_transaction_id ?? $this->transaction_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->when($request->route('id') !== null, $this->updated_at),
            'deleted_at' => $this->when($request->route('id') !== null, $this->deleted_at),
        ];
    }
}