<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthOtpRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CustomerAuthService
{
    public function __construct(private readonly AuthOtpRepository $authOtpRepository)
    {
    }

    public function requestOtp(string $channel, string $identifier): array
    {
        $channel = strtolower($channel);
        $otp = (string) random_int(100000, 999999);
        $this->authOtpRepository->createOtp($channel, $identifier, $otp);

        if ($channel === 'email') {
            Mail::raw("Your login OTP is: {$otp}\n\nIt expires in 5 minutes.", function ($message) use ($identifier) {
                $message->to($identifier)->subject('Your OTP');
            });
        } else {
            logger()->info('SMS OTP', ['mobile' => $identifier, 'otp' => $otp]);
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
        $otpRecord = $this->authOtpRepository->findValidOtp($channel, $identifier, $otp);
        if (! $otpRecord) {
            throw new UnauthorizedHttpException('', 'Invalid or expired OTP.');
        }

        $user = $this->findOrCreateCustomerByIdentifier($channel, $identifier);
        $this->markVerified($user, $channel);

        $this->authOtpRepository->consume($otpRecord);
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function register(array $data): array
    {
        $user = new User();
        $user->role = 'customer';
        $user->name = $data['name'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->mobile = $data['mobile'] ?? null;
        $user->password = $data['password'] ?? null;
        $user->pin = $data['pin'] ?? null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function loginWithPassword(string $identifier, string $password): array
    {
        $user = $this->findCustomerByEmailOrMobile($identifier);
        if (! $user || ! $user->password || ! password_verify($password, (string) $user->password)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return ['token' => $token, 'user' => $user];
    }

    public function loginWithPin(string $identifier, string $pin): array
    {
        $user = $this->findCustomerByEmailOrMobile($identifier);
        if (! $user || ! $user->pin || ! password_verify($pin, (string) $user->pin)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return ['token' => $token, 'user' => $user];
    }

    public function setPassword(User $user, string $password): User
    {
        $user->password = $password;
        $user->save();
        return $user->refresh();
    }

    public function setPin(User $user, string $pin): User
    {
        $user->pin = $pin;
        $user->save();
        return $user->refresh();
    }

    private function findCustomerByEmailOrMobile(string $identifier): ?User
    {
        return User::query()
            ->where('role', 'customer')
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)->orWhere('mobile', $identifier);
            })
            ->first();
    }

    private function findOrCreateCustomerByIdentifier(string $channel, string $identifier): User
    {
        $attrs = ['role' => 'customer'];
        $defaults = ['name' => 'Customer '.Str::substr(preg_replace('/\D+/', '', $identifier), -4)];

        if ($channel === 'email') {
            return User::firstOrCreate($attrs + ['email' => $identifier], $defaults);
        }

        return User::firstOrCreate($attrs + ['mobile' => $identifier], $defaults);
    }

    private function markVerified(User $user, string $channel): void
    {
        if ($channel === 'email' && ! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($channel !== 'email' && ! $user->mobile_verified_at) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }
    }
}

