<?php

namespace App\Repositories;

use App\Models\AuthOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthOtpRepository
{
    public function createOtp(string $channel, string $identifier, string $otp, int $expiresInMinutes = 5): AuthOtp
    {
        return AuthOtp::create([
            'channel' => $channel,
            'identifier' => $identifier,
            'otp_hash' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    public function findValidOtp(string $channel, string $identifier, string $otp): ?AuthOtp
    {
        $candidate = AuthOtp::query()
            ->where('channel', $channel)
            ->where('identifier', $identifier)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', Carbon::now())
            ->latest('id')
            ->first();

        if (! $candidate) {
            return null;
        }

        return Hash::check($otp, $candidate->otp_hash) ? $candidate : null;
    }

    public function consume(AuthOtp $authOtp): void
    {
        $authOtp->forceFill(['consumed_at' => Carbon::now()])->save();
    }
}

