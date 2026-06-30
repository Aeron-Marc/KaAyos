<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\WorkerController as ClientWorkerController;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\Api\PasswordOtpController;
use App\Http\Controllers\Api\ProfileController;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
});

RateLimiter::for('register', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function () {
    return view('search.index');
})->name('search');

Route::get('/services', function () {
    return view('services.index');
})->name('services.index');

Route::get('/login',  [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('throttle:register');

Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'store'])->name('password.update');

Route::middleware(['auth', 'verified', 'no-cache'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/notifications', [ClientController::class, 'notifications'])->name('dashboard.notifications');
    Route::get('/workers', [ClientWorkerController::class, 'index'])->name('workers');
    Route::get('/bookings', [ClientController::class, 'bookings'])->name('bookings');
    Route::get('/messages', [ClientController::class, 'messages'])->name('messages');
    Route::get('/reviews', [ClientController::class, 'reviews'])->name('reviews');
    Route::get('/account/profile', [ClientController::class, 'profile'])->name('account.profile');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/password-otp/send',   [PasswordOtpController::class, 'send']);
    Route::post('/password-otp/verify', [PasswordOtpController::class, 'verify']);
    Route::put('/api/profile',           [ProfileController::class, 'updateProfile']);
    Route::put('/api/preferences',       [ProfileController::class, 'updatePreferences']);
    Route::post('/api/profile/avatar',   [ProfileController::class, 'uploadAvatar']);
});

Route::middleware(['auth', 'verified', 'worker', 'no-cache'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/dashboard', [WorkerController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/notifications', [WorkerController::class, 'notifications'])->name('dashboard.notifications');
    Route::get('/jobs', [WorkerController::class, 'jobs'])->name('jobs');
    Route::get('/schedule', [WorkerController::class, 'schedule'])->name('schedule');
    Route::get('/messages', [WorkerController::class, 'messages'])->name('messages');
    Route::get('/earnings', [WorkerController::class, 'earnings'])->name('earnings');
    Route::get('/profile', [WorkerController::class, 'profile'])->name('profile');
    Route::put('/profile', [WorkerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [WorkerController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::post('/profile/portfolio', [WorkerController::class, 'uploadPortfolio'])->name('profile.portfolio');
    Route::delete('/profile/portfolio/{id}', [WorkerController::class, 'deletePortfolio'])->name('profile.portfolio.delete');
    Route::post('/profile/document', [WorkerController::class, 'uploadDocument'])->name('profile.document');
    Route::get('/documents', [WorkerController::class, 'documents'])->name('documents');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth', 'no-cache'])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();

    $role = $request->user()->role;

    return redirect($role === 'worker' ? route('worker.dashboard') : route('client.dashboard'))
        ->with('success', 'Email verified successfully!');
})->middleware(['auth', 'signed', 'no-cache'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1', 'no-cache'])->name('verification.send');

Route::get('/about',   function () { return view('pages.about'); })->name('about');
Route::get('/contact', function () { return view('pages.contact'); })->name('contact');
Route::get('/privacy', function () { return view('pages.privacy'); })->name('privacy');
Route::get('/terms',   function () { return view('pages.terms'); })->name('terms');
Route::get('/safety',  function () { return view('pages.safety'); })->name('safety');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/verification', function() {
        return view('admin.verification.index');
    })->name('verification.index');
    
    Route::get('/verification/{id}', function($id) {
        return view('admin.verification.show');
    })->name('verification.show');
    
    Route::post('/verification/{id}/approve', function($id) {
        return redirect()->route('admin.verification.index')->with('success', 'Verification approved successfully');
    })->name('verification.approve');
    
    Route::post('/verification/{id}/reject', function($id) {
        return redirect()->route('admin.verification.index')->with('error', 'Verification rejected');
    })->name('verification.reject');
});
