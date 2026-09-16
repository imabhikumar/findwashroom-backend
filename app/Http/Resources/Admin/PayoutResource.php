<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role,
            ] : null,
            'amount' => $this->amount,
            'status' => $this->status,
            'requested_at' => $this->requested_at,
            'processed_at' => $this->when($request->route('id') !== null, $this->processed_at),
        ];
    }
}