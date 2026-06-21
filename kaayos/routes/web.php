<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function () {
    // Wire up to your SearchController when ready
    return view('search.index');
})->name('search');

Route::get('/services', function () {
    return view('services.index');
})->name('services.index');

Route::get('/login',    function () { return view('auth.login'); })->name('login');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::get('/register/worker', function () { return view('auth.register-worker'); })->name('register.worker');

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