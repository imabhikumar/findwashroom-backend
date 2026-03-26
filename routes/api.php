<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CleaningJobController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReviewController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);


// Ye route public hai, iske liye login nahi chahiye
Route::get('/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API is working perfectly!',
        'user_lead' => 'Abhishek'
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
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