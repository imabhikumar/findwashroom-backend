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
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminActivityController;

// ============================================================
// PUBLIC ROUTES (No Authentication)
// ============================================================

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// OTP Routes
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
// Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);


Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Customer Auth (Public)
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login/otp/request', [CustomerAuthController::class, 'requestOtp']);
Route::post('/customer/login/otp/verify', [CustomerAuthController::class, 'verifyOtp']);
Route::post('/customer/login/password', [CustomerAuthController::class, 'loginWithPassword']);
Route::post('/customer/login/pin', [CustomerAuthController::class, 'loginWithPin']);

// Test Route
Route::get('/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API is working perfectly!',
        'user_lead' => 'Abhishek'
    ]);
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================

Route::middleware('auth:sanctum')->group(function () {
    // Customer Account
    Route::get('/customer/me', [CustomerAuthController::class, 'me']);
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
    Route::post('/customer/set-password', [CustomerAuthController::class, 'setPassword']);
    Route::post('/customer/set-pin', [CustomerAuthController::class, 'setPin']);

    // Property Owner Routes
    Route::post('/owner/properties', [PropertyController::class, 'store']);
    Route::get('/owner/properties', [PropertyController::class, 'myProperties']);
    Route::put('/owner/properties/{id}', [PropertyController::class, 'update']);

    // Booking Routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{id}/start', [BookingController::class, 'start']);
    Route::post('/bookings/{id}/end', [BookingController::class, 'end']);
    Route::get('/bookings', [BookingController::class, 'index']);

    // Payment Routes
    Route::post('/payments/order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);

    // Review Route
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Complaint Route
    Route::post('/complaints', [ComplaintController::class, 'store']);

    // Cleaning Job Routes
    Route::post('/owner/cleaning-jobs', [CleaningJobController::class, 'store']);
    Route::get('/cleaner/cleaning-jobs', [CleaningJobController::class, 'index']);
    Route::post('/cleaner/cleaning-jobs/{id}/accept', [CleaningJobController::class, 'accept']);
    Route::post('/cleaner/cleaning-jobs/{id}/proof', [CleaningJobController::class, 'uploadProof']);
});

// ============================================================
// PUBLIC PROPERTY ROUTES
// ============================================================

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);

// ============================================================
// ADMIN AUTH ROUTES
// ============================================================

Route::prefix('v1/admin')->group(function () {
    Route::middleware('admin.activity')->group(function () {
        Route::post('/login/otp/request', [AdminController::class, 'requestOtp']);
        Route::post('/login/otp/verify', [AdminController::class, 'verifyOtp']);
        Route::post('/login/pin', [AdminController::class, 'loginWithPin']);
    });

    Route::middleware(['admin.activity', 'auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/me', [AdminController::class, 'me']);
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::post('/set-pin', [AdminController::class, 'setPin']);

        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/activity', [AdminActivityController::class, 'index']);
        Route::get('/activity/suspicious', [AdminActivityController::class, 'suspicious']);
    });
});

// ============================================================
// V1 API ROUTES (Everything in one prefix)
// ============================================================

Route::prefix('v1')->group(function () {
    
    // ---------- WALLET ROUTES ----------
    // Public Wallet Routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'summary']);
        Route::get('/stats', [WalletController::class, 'stats']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/payout', [WalletController::class, 'requestPayout']);
    });
    
    // Admin Wallet Routes
    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/wallets', [WalletController::class, 'adminList']);
        Route::get('/wallets/{id}', [WalletController::class, 'show']);
        Route::get('/wallets/user/{userId}', [WalletController::class, 'getUserWallet']);
        Route::put('/wallets/{id}', [WalletController::class, 'updateBalance']);
        Route::put('/wallets/{id}/status', [WalletController::class, 'updateStatus']);
        Route::post('/wallets/{id}/add-funds', [WalletController::class, 'addFunds']);
        Route::post('/wallets/{id}/deduct-funds', [WalletController::class, 'deductFunds']);
        Route::get('/wallets/{id}/transactions', [WalletController::class, 'getWalletTransactions']);
    });

    // ---------- SERVICE UNIT ROUTES ----------
    Route::get('/properties/{propertyId}/service-units', [ServiceUnitController::class, 'index']);
    Route::get('/properties/{propertyId}/service-units/available', [ServiceUnitController::class, 'available']);
    Route::get('/service-units/types', [ServiceUnitController::class, 'types']);
    Route::get('/service-units/{id}', [ServiceUnitController::class, 'show']);

    // ---------- PRODUCT ROUTES ----------
    Route::get('/properties/{propertyId}/products', [ProductController::class, 'index']);
    Route::get('/properties/{propertyId}/products/available', [ProductController::class, 'available']);
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // ---------- PARTNER ROUTES (Auth Required) ----------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/partner/service-units', [ServiceUnitController::class, 'store']);
        Route::put('/partner/service-units/{id}', [ServiceUnitController::class, 'update']);
        Route::put('/partner/service-units/{id}/status/{status}', [ServiceUnitController::class, 'status']);

        Route::post('/partner/products', [ProductController::class, 'store']);
        Route::put('/partner/products/{id}', [ProductController::class, 'update']);
        Route::post('/partner/products/{id}/stock', [ProductController::class, 'updateStock']);
    });

    // ---------- TRUST ROUTES (Auth Required) ----------
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/trust/score', [TrustController::class, 'myTrustScore']);
        Route::get('/trust/badges', [TrustController::class, 'myBadges']);
        Route::get('/trust/summary', [TrustController::class, 'trustSummary']);
        Route::get('/trust/property/{propertyId}/badges', [TrustController::class, 'propertyBadges']);
    });

    // ---------- AUDIT LOG ROUTES (Admin Only) ----------
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
    });
});
Route::post('/auth/send-otp', function (Request $request) {
    // Immediately return a success so we know the API is reachable
    return response()->json([
        'success' => true, 
        'message' => 'API Connection is LIVE', 
        'otp' => '123456' 
    ]);
});