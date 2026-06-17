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