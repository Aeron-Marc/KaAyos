<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\WorkerController as ClientWorkerController;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\Worker\WorkerDashboardController;
use App\Http\Controllers\Api\PasswordOtpController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\WorkerController as AdminWorkerController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ProviderServiceController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\ReportController;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
});

RateLimiter::for('register', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});

RateLimiter::for('email-otp-send', function (Request $request) {
    return Limit::perHour(3)->by($request->user()->id);
});

RateLimiter::for('email-otp-verify', function (Request $request) {
    return Limit::perHour(5)->by($request->user()->id);
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/workers/{worker}', [App\Http\Controllers\Workers\PublicWorkerController::class, 'show'])->name('workers.public.show');

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
    Route::get('/workers/{worker}', [ClientWorkerController::class, 'show'])->name('workers.show');
    Route::get('/bookings', [ClientController::class, 'bookings'])->name('bookings');
    Route::post('/bookings', [ClientController::class, 'storeBooking'])->middleware('throttle:10,1')->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [ClientController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/review', [ClientController::class, 'submitReview'])->name('bookings.review');
    Route::post('/bookings/{booking}/reschedule', [ClientController::class, 'rescheduleRequest'])->name('bookings.reschedule');
    Route::post('/bookings/{booking}/reschedule-respond', [ClientController::class, 'respondReschedule'])->name('bookings.reschedule-respond');
    Route::get('/messages', [ClientController::class, 'messages'])->name('messages');
    Route::get('/messages/poll/{conversation}', [ClientController::class, 'pollMessages'])->middleware('throttle:30,1')->name('messages.poll');
    Route::post('/messages/send', [ClientController::class, 'sendMessage'])->middleware('throttle:30,1')->name('messages.send');
    Route::post('/messages/{conversation}/read', [ClientController::class, 'markMessagesRead'])->name('messages.read');
    Route::get('/reviews', [ClientController::class, 'reviews'])->name('reviews');
    Route::get('/suggestions', [ClientController::class, 'suggestions'])->name('suggestions');
    Route::get('/account/profile', [ClientController::class, 'profile'])->name('account.profile');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/password-otp/send',            [PasswordOtpController::class, 'send']);
    Route::post('/password-otp/verify',          [PasswordOtpController::class, 'verify']);
    Route::post('/email-otp/send',               [EmailOtpController::class, 'sendOtp'])
        ->middleware('throttle:email-otp-send');
    Route::post('/email-otp/verify',             [EmailOtpController::class, 'verifyOtp'])
        ->middleware('throttle:email-otp-verify');
    Route::put('/api/profile',                    [ProfileController::class, 'updateProfile']);
    Route::put('/api/preferences',                [ProfileController::class, 'updatePreferences']);
    Route::post('/api/profile/avatar',            [ProfileController::class, 'uploadAvatar']);
});

// Chatbot (authenticated)
Route::middleware('auth')->post('/api/chat', [App\Http\Controllers\Api\ChatBotController::class, '__invoke']);

// Suggestions (authenticated) — uses ML + AI for worker recommendations
Route::middleware('auth')->post('/api/chat/suggest', [App\Http\Controllers\Api\SuggestionController::class, '__invoke']);

Route::middleware(['auth', 'verified', 'worker', 'no-cache'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/dashboard', [WorkerController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/notifications', [WorkerController::class, 'notifications'])->name('dashboard.notifications');
    Route::get('/jobs', [WorkerController::class, 'jobs'])->name('jobs');
    Route::get('/schedule', [WorkerController::class, 'schedule'])->name('schedule');
    Route::get('/messages', [WorkerController::class, 'messages'])->name('messages');
    Route::get('/messages/poll/{conversation}', [WorkerController::class, 'pollMessages'])->middleware('throttle:30,1')->name('messages.poll');
    Route::post('/messages/send', [WorkerController::class, 'sendMessage'])->middleware('throttle:30,1')->name('messages.send');
    Route::post('/messages/{conversation}/read', [WorkerController::class, 'markMessagesRead'])->name('messages.read');
    Route::get('/earnings', [WorkerController::class, 'earnings'])->name('earnings');
    Route::get('/earnings/export', [WorkerController::class, 'exportEarnings'])->name('earnings.export');
    Route::get('/profile', [WorkerController::class, 'profile'])->name('profile');
    Route::put('/profile', [WorkerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [WorkerController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::post('/profile/portfolio', [WorkerController::class, 'uploadPortfolio'])->name('profile.portfolio');
    Route::delete('/profile/portfolio/{id}', [WorkerController::class, 'deletePortfolio'])->name('profile.portfolio.delete');
    Route::post('/profile/document', [WorkerController::class, 'uploadDocument'])->name('profile.document');
    Route::get('/documents', [WorkerController::class, 'documents'])->name('documents');

    // Dashboard API endpoints
    Route::get('/dashboard/data', [WorkerDashboardController::class, 'dashboard'])->name('dashboard.data');
    Route::patch('/jobs/{booking}/status', [WorkerDashboardController::class, 'updateJobStatus'])->name('jobs.status');
    Route::post('/jobs/{booking}/photo', [WorkerDashboardController::class, 'uploadPhoto'])->name('jobs.photo');
    Route::post('/jobs/{booking}/cancel', [WorkerDashboardController::class, 'cancelJob'])->name('jobs.cancel');
    Route::post('/jobs/{booking}/reschedule', [WorkerDashboardController::class, 'rescheduleRequest'])->name('jobs.reschedule');
    Route::post('/jobs/{booking}/reschedule-respond', [WorkerDashboardController::class, 'respondReschedule'])->name('jobs.reschedule-respond');
    Route::put('/location', [WorkerDashboardController::class, 'updateLocation'])->name('location.update');
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
Route::middleware(['auth', 'verified', 'admin', 'no-cache'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

    // Workers
    Route::get('/workers', [App\Http\Controllers\Admin\WorkerController::class, 'index'])->name('workers.index');

    // Verifications
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{verification}', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verification/{verification}/approve', [VerificationController::class, 'approve'])->name('verification.approve');
    Route::post('/verification/{verification}/reject', [VerificationController::class, 'reject'])->name('verification.reject');

    // Service Categories
    Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories.index');
    Route::get('/service-categories/create', [ServiceCategoryController::class, 'create'])->name('service-categories.create');
    Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
    Route::get('/service-categories/{serviceCategory}/edit', [ServiceCategoryController::class, 'edit'])->name('service-categories.edit');
    Route::put('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('service-categories.update');
    Route::delete('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Provider Services
    Route::get('/provider-services', [ProviderServiceController::class, 'index'])->name('provider-services.index');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::put('/disputes/{dispute}', [DisputeController::class, 'update'])->name('disputes.update');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});
