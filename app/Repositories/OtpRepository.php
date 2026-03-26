<?php

namespace App\Repositories;

use App\Models\Otp;
use Carbon\Carbon;

class OtpRepository
{
    public function createOtp(string $mobile, string $otp): Otp
    {
        return Otp::create([
            'mobile' => $mobile,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);
    }

    public function findValidOtp(string $mobile, string $otp): ?Otp
    {
        return Otp::query()
            ->where('mobile', $mobile)
            ->where('otp', $otp)
            ->where('expires_at', '>=', Carbon::now())
            ->latest('id')
            ->first();
    }
}
