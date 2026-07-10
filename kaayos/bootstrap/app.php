<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/api/*',
            '/password-otp/*',
            '/email-otp/*',
        ]);

        $middleware->alias([
            'worker'   => \App\Http\Middleware\CheckWorkerRole::class,
            'admin'    => \App\Http\Middleware\CheckAdminRole::class,
            'no-cache' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson(),
        );
    })->create();
