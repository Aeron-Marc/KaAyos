<?php

namespace App\Providers;

use App\Support\ClientSampleData;
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
        View::composer('layouts.client', function ($view) {
            $view->with('notifications', ClientSampleData::notifications());
        });
    }
}
