<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && in_array($request->user()->language, ['English', 'Filipino'])) {
            $locale = $request->user()->language === 'Filipino' ? 'fil' : 'en';
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
