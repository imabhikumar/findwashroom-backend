<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Models\AdminOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminRepository
{
    public function findAdminByIdentifier(string $identifier): ?Admin
    {
        return Admin::query()
            ->where('role', 'admin')
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)->orWhere('mobile', $identifier);
            })
            ->first();
    }

    public function findOrCreateAdminByOtp(string $channel, string $identifier): Admin
    {
        // IMPORTANT: users.email and users.mobile are unique.
        // If the identifier exists for a non-admin role, we must not attempt to insert
        // a new admin row (it would violate unique constraints).
        $existingUser = User::query()
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)->orWhere('mobile', $identifier);
            })
            ->first();

        if ($existingUser) {
            if (($existingUser->role ?? null) !== 'admin') {
                // Caller will treat this as "not authorized".
                throw new \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('', 'Not an admin.');
            }

            /** @var Admin $existingUser */
            return $existingUser;
        }

        $defaults = [
            'name' => 'Admin ' . Str::substr(preg_replace('/\\D+/', '', $identifier), -4),
            'role' => 'admin',
        ];

        if ($channel === 'email') {
            return Admin::query()->firstOrCreate(['role' => 'admin', 'email' => $identifier], $defaults);
        }

        return Admin::query()->firstOrCreate(['role' => 'admin', 'mobile' => $identifier], $defaults);
    }

    public function createAdminOtp(string $channel, string $identifier, string $otp, int $expiresInMinutes = 5): AdminOtp
    {
        return AdminOtp::create([
            'channel' => $channel,
            'identifier' => $identifier,
            'otp_hash' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    public function findValidAdminOtp(string $channel, string $identifier, string $otp): ?AdminOtp
    {
        $candidate = AdminOtp::query()
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

    public function consumeAdminOtp(AdminOtp $otp): void
    {
        $otp->forceFill(['consumed_at' => Carbon::now()])->save();
    }
}

