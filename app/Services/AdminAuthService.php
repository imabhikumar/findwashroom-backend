<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminAuthService
{
    public function __construct(private readonly AdminRepository $adminRepository)
    {
    }

    public function requestOtp(string $channel, string $identifier): array
    {
        $channel = strtolower($channel);
        $otp = (string) random_int(100000, 999999);

        $this->adminRepository->createAdminOtp($channel, $identifier, $otp);

        if ($channel === 'email') {
            try {
                Mail::raw("Your admin login OTP is: {$otp}\n\nIt expires in 5 minutes.", function ($message) use ($identifier) {
                    $message->to($identifier)->subject('FindWashroom - Admin OTP');
                });
            } catch (\Throwable $e) {
                logger()->warning('Failed to send admin OTP email', ['error' => $e->getMessage()]);
            }
        } else {
            // In production you would integrate an SMS provider here.
            logger()->info('Admin SMS OTP generated', ['mobile' => $identifier]);
        }

        $data = [
            'channel' => $channel,
            'identifier' => $identifier,
            'expires_in_minutes' => 5,
        ];

        if (config('app.debug')) {
            $data['otp'] = $otp;
        }

        return $data;
    }

    public function verifyOtpAndLogin(string $channel, string $identifier, string $otp): array
    {
        $channel = strtolower($channel);
        $otpRecord = $this->adminRepository->findValidAdminOtp($channel, $identifier, $otp);

        if (! $otpRecord) {
            throw new UnauthorizedHttpException('', 'Invalid or expired OTP.');
        }

        $admin = $this->adminRepository->findOrCreateAdminByOtp($channel, $identifier);

        $this->adminRepository->consumeAdminOtp($otpRecord);

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return [
            'token' => $token,
            'admin' => $admin,
        ];
    }

    public function loginWithPin(string $identifier, string $pin): array
    {
        $admin = $this->adminRepository->findAdminByIdentifier($identifier);
        if (! $admin || ! $admin->pin) {
            throw new UnauthorizedHttpException('', 'Invalid credentials.');
        }

        if (! Hash::check($pin, (string) $admin->pin)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials.');
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return [
            'token' => $token,
            'admin' => $admin,
        ];
    }

    public function setPin(User $admin, string $pin): User
    {
        $admin->pin = $pin; // User model casts pin as hashed.
        $admin->save();
        return $admin->refresh();
    }
}

