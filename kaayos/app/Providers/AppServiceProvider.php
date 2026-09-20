<?php

namespace App\Providers;

use App\Support\ClientSampleData;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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

        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        View::composer('layouts.client', function ($view) {
            $view->with('notifications', ClientSampleData::notifications());
        });
    }
}
