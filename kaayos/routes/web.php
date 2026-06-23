<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\WorkerController as ClientWorkerController;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\Api\PasswordOtpController;
use App\Http\Controllers\Api\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function () {
    return view('search.index');
})->name('search');

Route::get('/services', function () {
    return view('services.index');
})->name('services.index');

Route::get('/login',  [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
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

Route::middleware(['auth'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/dashboard', [WorkerController::class, 'dashboard'])->name('dashboard');
});

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
        // Handle approve logic here
        return redirect()->route('admin.verification.index')->with('success', 'Verification approved successfully');
    })->name('verification.approve');
    
    Route::post('/verification/{id}/reject', function($id) {
        // Handle reject logic here
        return redirect()->route('admin.verification.index')->with('error', 'Verification rejected');
    })->name('verification.reject');
});
