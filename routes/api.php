<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminActivityController;
use App\Http\Controllers\Api\AuditLogController;
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
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminPropertyController;
use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminPayoutController;
use App\Http\Controllers\Api\Admin\AdminComplaintController;
use App\Http\Controllers\Api\Admin\AdminDisputeController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminTrustController;
use App\Http\Controllers\Api\Admin\AdminBadgeController;
use App\Http\Controllers\Api\Admin\AdminSafetyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Laravel automatically loads this file with the /api prefix.
| Therefore Route::prefix('v1') becomes /api/v1/...
|
| IMPORTANT: Do NOT add another /v1 prefix inside this file.
|
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customer/me', [CustomerAuthController::class, 'me']);
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
    Route::post('/customer/set-password', [CustomerAuthController::class, 'setPassword']);
    Route::post('/customer/set-pin', [CustomerAuthController::class, 'setPin']);
});

Route::prefix('v1')->group(function () {

    // Public authentication
    Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

    // Customer authentication
    Route::post('/customer/register', [CustomerAuthController::class, 'register']);
    Route::post('/customer/login/otp/request', [CustomerAuthController::class, 'requestOtp']);
    Route::post('/customer/login/otp/verify', [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/customer/login/password', [CustomerAuthController::class, 'loginWithPassword']);
    Route::post('/customer/login/pin', [CustomerAuthController::class, 'loginWithPin']);

    // Public browsing
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::get('/properties/{propertyId}/service-units', [ServiceUnitController::class, 'index']);
    Route::get('/properties/{propertyId}/service-units/available', [ServiceUnitController::class, 'available']);
    Route::get('/service-units/types', [ServiceUnitController::class, 'types']);
    Route::get('/service-units/{id}', [ServiceUnitController::class, 'show']);
    Route::get('/properties/{propertyId}/products', [ProductController::class, 'index']);
    Route::get('/properties/{propertyId}/products/available', [ProductController::class, 'available']);
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Authenticated customer / partner operations
    Route::middleware('auth:sanctum')->group(function () {

        // Customer account
        Route::get('/customer/me', [CustomerAuthController::class, 'me']);
        Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
        Route::post('/customer/set-password', [CustomerAuthController::class, 'setPassword']);
        Route::post('/customer/set-pin', [CustomerAuthController::class, 'setPin']);

        // Property owner
        Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
            Route::post('/owner/properties', [PropertyController::class, 'store']);
            Route::get('/owner/properties', [PropertyController::class, 'myProperties']);
            Route::put('/owner/properties/{id}', [PropertyController::class, 'update']);
        });

        // Bookings
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/{id}/start', [BookingController::class, 'start']);
        Route::post('/bookings/{id}/end', [BookingController::class, 'end']);
        Route::get('/bookings', [BookingController::class, 'index']);

        // Payments
        Route::post('/payments/order', [PaymentController::class, 'createOrder']);
        Route::post('/payments/verify', [PaymentController::class, 'verify']);

        // Reviews / complaints
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::post('/complaints', [ComplaintController::class, 'store']);

        // Cleaning jobs
        Route::post('/owner/cleaning-jobs', [CleaningJobController::class, 'store']);
        Route::get('/cleaner/cleaning-jobs', [CleaningJobController::class, 'index']);
        Route::post('/cleaner/cleaning-jobs/{id}/accept', [CleaningJobController::class, 'accept']);
        Route::post('/cleaner/cleaning-jobs/{id}/proof', [CleaningJobController::class, 'uploadProof']);

        // Wallet
        Route::get('/wallet', [WalletController::class, 'summary']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
        Route::post('/wallet/add-money', [WalletController::class, 'addMoney']);
        Route::post('/wallet/request-payout', [WalletController::class, 'requestPayout']);

        // Partner service units
        Route::post('/partner/service-units', [ServiceUnitController::class, 'store']);
        Route::put('/partner/service-units/{id}', [ServiceUnitController::class, 'update']);
        Route::put('/partner/service-units/{id}/status/{status}', [ServiceUnitController::class, 'status']);

        // Partner products
        Route::post('/partner/products', [ProductController::class, 'store']);
        Route::put('/partner/products/{id}', [ProductController::class, 'update']);
        Route::post('/partner/products/{id}/stock', [ProductController::class, 'updateStock']);

        // Trust
        Route::get('/trust/score', [TrustController::class, 'myTrustScore']);
        Route::get('/trust/badges', [TrustController::class, 'myBadges']);
        Route::get('/trust/summary', [TrustController::class, 'trustSummary']);
        Route::get('/trust/property/{propertyId}/badges', [TrustController::class, 'propertyBadges']);
    });

    // Admin authentication and protected admin APIs
    Route::prefix('admin')->group(function () {

        // Public admin login endpoints; activity middleware logs attempts
        Route::middleware('admin.activity')->group(function () {
            Route::post('/login/otp/request', [AdminController::class, 'requestOtp']);
            Route::post('/login/otp/verify', [AdminController::class, 'verifyOtp']);
            Route::post('/login/pin', [AdminController::class, 'loginWithPin']);
        });

        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::post('/logout', [AdminController::class, 'logout']);
            Route::get('/me', [AdminController::class, 'me']);
            Route::post('/set-pin', [AdminController::class, 'setPin']);

            Route::get('/users', [AdminUserController::class, 'index']);
            Route::post('/users', [AdminUserController::class, 'store']);
            Route::get('/users/{id}', [AdminUserController::class, 'show']);
            Route::put('/users/{id}', [AdminUserController::class, 'update']);
            Route::post('/users/{id}/suspend', [AdminUserController::class, 'suspend']);
            Route::post('/users/{id}/ban', [AdminUserController::class, 'ban']);
            Route::post('/users/{id}/reactivate', [AdminUserController::class, 'reactivate']);
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

            Route::get('/properties', [AdminPropertyController::class, 'index']);
            Route::post('/properties', [AdminPropertyController::class, 'store']);
            Route::get('/properties/{id}', [AdminPropertyController::class, 'show']);
            Route::put('/properties/{id}', [AdminPropertyController::class, 'update']);
            Route::post('/properties/{id}/approve', [AdminPropertyController::class, 'approve']);
            Route::post('/properties/{id}/reject', [AdminPropertyController::class, 'reject']);
            Route::post('/properties/{id}/suspend', [AdminPropertyController::class, 'suspend']);
            Route::post('/properties/{id}/ban', [AdminPropertyController::class, 'ban']);
            Route::delete('/properties/{id}', [AdminPropertyController::class, 'destroy']);

            Route::get('/bookings/stats', [AdminBookingController::class, 'stats']);
            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
            Route::post('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel']);
            Route::post('/bookings/{id}/force-complete', [AdminBookingController::class, 'forceComplete']);
            Route::post('/bookings/{id}/extend', [AdminBookingController::class, 'extend']);

            Route::get('/payments/stats', [AdminPaymentController::class, 'stats']);
            Route::get('/payments', [AdminPaymentController::class, 'index']);
            Route::get('/payments/{id}', [AdminPaymentController::class, 'show']);
            Route::post('/payments/{id}/refund', [AdminPaymentController::class, 'refund']);

            Route::get('/payouts', [AdminPayoutController::class, 'index']);
            Route::post('/payouts/{id}/approve', [AdminPayoutController::class, 'approve']);
            Route::post('/payouts/{id}/reject', [AdminPayoutController::class, 'reject']);

            Route::get('/complaints/stats', [AdminComplaintController::class, 'stats']);
            Route::get('/complaints', [AdminComplaintController::class, 'index']);
            Route::get('/complaints/{id}', [AdminComplaintController::class, 'show']);
            Route::put('/complaints/{id}', [AdminComplaintController::class, 'update']);
            Route::post('/complaints/{id}/resolve', [AdminComplaintController::class, 'resolve']);
            Route::post('/complaints/{id}/escalate', [AdminComplaintController::class, 'escalate']);

            Route::post('/disputes/{id}/resolve', [AdminDisputeController::class, 'resolve']);
            Route::post('/disputes/{id}/reject-appeal', [AdminDisputeController::class, 'rejectAppeal']);

            Route::get('/settings', [AdminSettingsController::class, 'index']);
            Route::put('/settings', [AdminSettingsController::class, 'update']);

            Route::get('/settings/commission-rules', [AdminSettingsController::class, 'commissionRules']);
            Route::post('/settings/commission-rules', [AdminSettingsController::class, 'storeCommission']);
            Route::put('/settings/commission-rules/{id}', [AdminSettingsController::class, 'updateCommission']);
            Route::delete('/settings/commission-rules/{id}', [AdminSettingsController::class, 'deleteCommission']);

            Route::get('/settings/refund-rules', [AdminSettingsController::class, 'refundRules']);
            Route::post('/settings/refund-rules', [AdminSettingsController::class, 'storeRefund']);
            Route::put('/settings/refund-rules/{id}', [AdminSettingsController::class, 'updateRefund']);
            Route::delete('/settings/refund-rules/{id}', [AdminSettingsController::class, 'deleteRefund']);

            Route::get('/settings/cancellation-rules', [AdminSettingsController::class, 'cancellationRules']);
            Route::post('/settings/cancellation-rules', [AdminSettingsController::class, 'storeCancellation']);
            Route::put('/settings/cancellation-rules/{id}', [AdminSettingsController::class, 'updateCancellation']);
            Route::delete('/settings/cancellation-rules/{id}', [AdminSettingsController::class, 'deleteCancellation']);

            Route::get('/reports/bookings', [AdminReportController::class, 'bookings']);
            Route::get('/reports/revenue', [AdminReportController::class, 'revenue']);
            Route::get('/reports/complaints', [AdminReportController::class, 'complaints']);
            Route::get('/reports/trust', [AdminReportController::class, 'trust']);
            Route::get('/reports/safety', [AdminReportController::class, 'safety']);
            Route::get('/reports/export', [AdminReportController::class, 'export']);

            Route::get('/trust/events', [AdminTrustController::class, 'events']);
            Route::get('/trust/users', [AdminTrustController::class, 'users']);
            Route::get('/trust/users/{id}', [AdminTrustController::class, 'show']);
            Route::post('/trust/events', [AdminTrustController::class, 'storeEvent']);

            Route::get('/badges', [AdminBadgeController::class, 'index']);
            Route::post('/badges', [AdminBadgeController::class, 'store']);
            Route::post('/badges/assign', [AdminBadgeController::class, 'assign']);
            Route::post('/badges/revoke', [AdminBadgeController::class, 'revoke']);
            Route::put('/badges/{id}', [AdminBadgeController::class, 'update']);
            Route::delete('/badges/{id}', [AdminBadgeController::class, 'destroy']);

            Route::get('/safety/sos', [AdminSafetyController::class, 'sos']);
            Route::get('/safety/sos/{id}', [AdminSafetyController::class, 'showSos']);
            Route::post('/safety/sos/{id}/acknowledge', [AdminSafetyController::class, 'acknowledge']);
            Route::post('/safety/sos/{id}/resolve', [AdminSafetyController::class, 'resolveSos']);
            Route::post('/safety/sos/{id}/false-alarm', [AdminSafetyController::class, 'falseAlarm']);
            Route::get('/safety/incidents', [AdminSafetyController::class, 'incidents']);
            Route::get('/safety/incidents/{id}', [AdminSafetyController::class, 'showIncident']);
            Route::post('/safety/incidents', [AdminSafetyController::class, 'storeIncident']);
            Route::put('/safety/incidents/{id}', [AdminSafetyController::class, 'updateIncident']);
            Route::post('/safety/incidents/{id}/escalate', [AdminSafetyController::class, 'escalateIncident']);
            Route::get('/safety/reports', [AdminSafetyController::class, 'reports']);
            Route::get('/safety/stats', [AdminSafetyController::class, 'stats']);

            Route::get('/dashboard', [AdminDashboardController::class, 'index']);
            Route::get('/activity', [AdminActivityController::class, 'index']);
            Route::get('/activity/suspicious', [AdminActivityController::class, 'suspicious']);

            // Admin wallet management
            Route::get('/wallets', [WalletController::class, 'adminList']);
            Route::put('/wallets/{id}/status', [WalletController::class, 'updateStatus']);
            Route::post('/wallets/{id}/adjust', [WalletController::class, 'adjustBalance']);

            // Audit logs
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        });
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/v1/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/v1/audit-logs/{id}', [AuditLogController::class, 'show']);
});

// Simple API health/test endpoint
Route::get('/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API is working perfectly!',
    ]);
});
