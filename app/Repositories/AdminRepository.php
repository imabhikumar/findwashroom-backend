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

    public function findOrCreateAdminByOtp(string $identifier): User
    {
        // Try to find admin
        $admin = User::where('email', $identifier)
            ->where('role', 'admin')
            ->first();
            
        if (!$admin) {
            // Create admin if not exists
            $admin = User::create([
                'name' => 'Admin User',
                'email' => $identifier,
                'role' => 'admin',
                'status' => 'active',
            ]);
        }
        
        return $admin;
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

