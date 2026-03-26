<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAuth\CustomerRegisterRequest;
use App\Http\Requests\CustomerAuth\CustomerLoginPasswordRequest;
use App\Http\Requests\CustomerAuth\CustomerLoginPinRequest;
use App\Http\Requests\CustomerAuth\CustomerRequestOtpRequest;
use App\Http\Requests\CustomerAuth\CustomerVerifyOtpRequest;
use App\Http\Requests\CustomerAuth\SetPasswordRequest;
use App\Http\Requests\CustomerAuth\SetPinRequest;
use App\Services\CustomerAuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CustomerAuthController extends Controller
{
    public function __construct(private readonly CustomerAuthService $customerAuthService)
    {
    }

    public function register(CustomerRegisterRequest $request)
    {
        $data = $this->customerAuthService->register($request->validated());
        return $this->successResponse('Registration successful.', $data, 201);
    }

    public function requestOtp(CustomerRequestOtpRequest $request)
    {
        $data = $this->customerAuthService->requestOtp(
            $request->validated('channel'),
            $request->validated('identifier'),
        );
        return $this->successResponse('OTP sent successfully.', $data);
    }

    public function verifyOtp(CustomerVerifyOtpRequest $request)
    {
        try {
            $data = $this->customerAuthService->verifyOtpAndLogin(
                $request->validated('channel'),
                $request->validated('identifier'),
                $request->validated('otp'),
            );
            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function loginWithPassword(CustomerLoginPasswordRequest $request)
    {
        try {
            $data = $this->customerAuthService->loginWithPassword(
                $request->validated('identifier'),
                $request->validated('password'),
            );
            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function loginWithPin(CustomerLoginPinRequest $request)
    {
        try {
            $data = $this->customerAuthService->loginWithPin(
                $request->validated('identifier'),
                $request->validated('pin'),
            );
            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function setPassword(SetPasswordRequest $request)
    {
        $user = $this->customerAuthService->setPassword($request->user(), $request->validated('password'));
        return $this->successResponse('Password updated.', ['user' => $user]);
    }

    public function setPin(SetPinRequest $request)
    {
        $user = $this->customerAuthService->setPin($request->user(), $request->validated('pin'));
        return $this->successResponse('PIN updated.', ['user' => $user]);
    }

    public function me(Request $request)
    {
        return $this->successResponse('OK', ['user' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return $this->successResponse('Logged out.');
    }
}

