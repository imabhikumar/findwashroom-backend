<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CleaningJobController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReviewController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Customer auth module
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login/otp/request', [CustomerAuthController::class, 'requestOtp']);
Route::post('/customer/login/otp/verify', [CustomerAuthController::class, 'verifyOtp']);
Route::post('/customer/login/password', [CustomerAuthController::class, 'loginWithPassword']);
Route::post('/customer/login/pin', [CustomerAuthController::class, 'loginWithPin']);


// Ye route public hai, iske liye login nahi chahiye
Route::get('/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API is working perfectly!',
        'user_lead' => 'Abhishek'
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // Customer account
    Route::get('/customer/me', [CustomerAuthController::class, 'me']);
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
    Route::post('/customer/set-password', [CustomerAuthController::class, 'setPassword']);
    Route::post('/customer/set-pin', [CustomerAuthController::class, 'setPin']);

    // Property owner routes
    Route::post('/owner/properties', [PropertyController::class, 'store']);
    Route::get('/owner/properties', [PropertyController::class, 'myProperties']);
    Route::put('/owner/properties/{id}', [PropertyController::class, 'update']);

    // Booking routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{id}/start', [BookingController::class, 'start']);
    Route::post('/bookings/{id}/end', [BookingController::class, 'end']);
    Route::get('/bookings', [BookingController::class, 'index']);

    // Payment routes
    Route::post('/payments/order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);

    // Review route
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Complaint route
    Route::post('/complaints', [ComplaintController::class, 'store']);

    // Cleaning job routes
    Route::post('/owner/cleaning-jobs', [CleaningJobController::class, 'store']);
    Route::get('/cleaner/cleaning-jobs', [CleaningJobController::class, 'index']);
    Route::post('/cleaner/cleaning-jobs/{id}/accept', [CleaningJobController::class, 'accept']);
    Route::post('/cleaner/cleaning-jobs/{id}/proof', [CleaningJobController::class, 'uploadProof']);
});

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);

// Admin auth (API v1)
Route::prefix('v1/admin')->group(function () {
    // Apply activity logging to all admin endpoints (even public login attempts).
    Route::middleware('admin.activity')->group(function () {
        Route::post('/login/otp/request', [AdminController::class, 'requestOtp']);
        Route::post('/login/otp/verify', [AdminController::class, 'verifyOtp']);
        Route::post('/login/pin', [AdminController::class, 'loginWithPin']);
    });

    Route::middleware(['admin.activity', 'auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/me', [AdminController::class, 'me']);
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::post('/set-pin', [AdminController::class, 'setPin']);

        Route::get('/dashboard', [\App\Http\Controllers\Api\AdminDashboardController::class, 'index']);
        Route::get('/activity', [\App\Http\Controllers\Api\AdminActivityController::class, 'index']);
        Route::get('/activity/suspicious', [\App\Http\Controllers\Api\AdminActivityController::class, 'suspicious']);
    });
});