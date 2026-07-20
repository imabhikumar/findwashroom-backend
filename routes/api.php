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
use App\Http\Controllers\Api\TrustController;
use App\Http\Controllers\Api\ServiceUnitController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WalletController;

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

    // routes/api.php - Add these lines
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'summary']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/payout', [WalletController::class, 'requestPayout']);
    
    // Admin wallet management
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/wallets', [WalletController::class, 'adminList']);
        Route::put('/wallets/{id}/status', [WalletController::class, 'updateStatus']);
        Route::post('/wallets/{id}/adjust', [WalletController::class, 'adjustBalance']);
    });
});
});
// routes/api.php - Add these lines
Route::prefix('v1')->group(function () {
    // Public routes for browsing
    Route::get('/properties/{propertyId}/service-units', [ServiceUnitController::class, 'index']);
    Route::get('/properties/{propertyId}/service-units/available', [ServiceUnitController::class, 'available']);
    Route::get('/service-units/types', [ServiceUnitController::class, 'types']);
    Route::get('/service-units/{id}', [ServiceUnitController::class, 'show']);

    Route::get('/properties/{propertyId}/products', [ProductController::class, 'index']);
    Route::get('/properties/{propertyId}/products/available', [ProductController::class, 'available']);
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Protected partner routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/partner/service-units', [ServiceUnitController::class, 'store']);
        Route::put('/partner/service-units/{id}', [ServiceUnitController::class, 'update']);
        Route::put('/partner/service-units/{id}/status/{status}', [ServiceUnitController::class, 'status']);

        Route::post('/partner/products', [ProductController::class, 'store']);
        Route::put('/partner/products/{id}', [ProductController::class, 'update']);
        Route::post('/partner/products/{id}/stock', [ProductController::class, 'updateStock']);
    });
    // routes/api.php - Add these lines
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/trust/score', [TrustController::class, 'myTrustScore']);
    Route::get('/trust/badges', [TrustController::class, 'myBadges']);
    Route::get('/trust/summary', [TrustController::class, 'trustSummary']);
    Route::get('/trust/property/{propertyId}/badges', [TrustController::class, 'propertyBadges']);
});
// routes/api.php - Add these lines
Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
});
});