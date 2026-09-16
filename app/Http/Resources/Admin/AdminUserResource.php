<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];

        if ($request->route('id') !== null) {
            $data += [
                'gender' => $this->gender,
                'dob' => $this->dob,
                'profile_image' => $this->profile_image,
                'last_active_at' => $this->last_active_at ?? null,
            ];
        }

        return $data;
    }
}