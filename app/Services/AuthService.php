<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OtpRepository;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(private readonly OtpRepository $otpRepository)
    {
    }

    public function sendOtp(string $mobile): array
    {
        $otp = (string) random_int(100000, 999999);
        $this->otpRepository->createOtp($mobile, $otp);

        return [
            'otp' => $otp,
            'expires_in_minutes' => 5,
        ];
    }

    public function verifyOtp(string $mobile, string $otp): array
    {
        $otpRecord = $this->otpRepository->findValidOtp($mobile, $otp);
        if (! $otpRecord) {
            throw new UnauthorizedHttpException('', 'Invalid or expired OTP.');
        }

        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            ['name' => 'User '.Str::substr($mobile, -4)]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
