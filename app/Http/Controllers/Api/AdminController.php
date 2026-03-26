<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginPinRequest;
use App\Http\Requests\Admin\AdminOtpRequest;
use App\Http\Requests\Admin\AdminOtpVerifyRequest;
use App\Http\Requests\Admin\AdminSetPinRequest;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminController extends Controller
{
    public function __construct(private readonly AdminAuthService $adminAuthService)
    {
    }

    public function requestOtp(AdminOtpRequest $request)
    {
        $data = $this->adminAuthService->requestOtp(
            $request->validated('channel'),
            $request->validated('identifier'),
        );

        return $this->successResponse('OTP sent successfully.', $data);
    }

    public function verifyOtp(AdminOtpVerifyRequest $request)
    {
        try {
            $data = $this->adminAuthService->verifyOtpAndLogin(
                $request->validated('channel'),
                $request->validated('identifier'),
                $request->validated('otp'),
            );

            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function loginWithPin(AdminLoginPinRequest $request)
    {
        try {
            $data = $this->adminAuthService->loginWithPin(
                $request->validated('identifier'),
                $request->validated('pin'),
            );

            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function setPin(AdminSetPinRequest $request)
    {
        $admin = $request->user();
        $data = $this->adminAuthService->setPin($admin, $request->validated('pin'));

        return $this->successResponse('PIN updated.', ['admin' => $data]);
    }

    public function me(Request $request)
    {
        return $this->successResponse('OK', ['admin' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return $this->successResponse('Logged out.');
    }
}

