<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WorkerApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/verify-otp', [AuthController::class, 'verifyEmailOtp']);
    Route::post('/resend-otp', [AuthController::class, 'sendVerificationOtp']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/workers', [CatalogController::class, 'workers']);
Route::get('/workers/{id}', [CatalogController::class, 'worker'])->whereNumber('id');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{booking}/status', [BookingController::class, 'status']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/confirm-complete', [BookingController::class, 'confirmComplete']);
    Route::post('/bookings/{booking}/review', [BookingController::class, 'review']);
    Route::post('/bookings/{booking}/report', [BookingController::class, 'report']);
    Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule']);
    Route::post('/bookings/{booking}/reschedule-respond', [BookingController::class, 'respondReschedule']);

    Route::get('/conversations', [ChatController::class, 'index']);
    Route::post('/conversations/start', [ChatController::class, 'start']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'send']);
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markRead']);

    Route::prefix('worker')->group(function () {
        Route::get('/stats', [WorkerApiController::class, 'stats']);
        Route::get('/earnings', [WorkerApiController::class, 'earnings']);
        Route::get('/profile', [WorkerApiController::class, 'profile']);
        Route::put('/profile', [WorkerApiController::class, 'updateProfile']);
    });

    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/preferences', [ProfileController::class, 'updatePreferences']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
});
