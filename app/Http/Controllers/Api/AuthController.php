<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $data = $this->authService->sendOtp($request->validated('mobile'));
        return $this->successResponse('OTP sent successfully.', $data);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $data = $this->authService->verifyOtp(
                $request->validated('mobile'),
                $request->validated('otp')
            );
            return $this->successResponse('Login successful.', $data);
        } catch (UnauthorizedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }
}